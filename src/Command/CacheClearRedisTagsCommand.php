<?php

namespace Tourze\Symfony\AopCacheBundle\Command;

use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\Adapter\TraceableTagAwareAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\VarExporter\LazyObjectInterface;
use Tourze\Symfony\CacheHotKey\Service\HotkeySmartCache;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask(expression: '10 5 * * *')]
#[AsCommand(name: self::NAME, description: '定期清理Redis缓存Tag')]
class CacheClearRedisTagsCommand extends Command
{
    public const NAME = 'cache:redis-clear-tags';
    public function __construct(
        #[Autowire(service: 'cache.app')]
        private readonly TagAwareAdapterInterface $cache,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pool = $this->cache;
        if ($pool instanceof TraceableTagAwareAdapter) {
            // TraceableTagAwareAdapter does not have getPool() method
            // We need to access the decorated adapter through reflection
            $reflection = new \ReflectionClass($pool);
            $property = $reflection->getProperty('pool');
            $property->setAccessible(true);
            $pool = $property->getValue($pool);
        }
        if ($pool instanceof HotkeySmartCache) {
            $pool = $pool->getDecorated();
        }
        if ($pool instanceof LazyObjectInterface) {
            $pool->initializeLazyObject();
        }

        if ($pool instanceof RedisTagAwareAdapter) {
            $this->clearTags($pool, $output);
            return Command::SUCCESS;
        }

        $output->writeln('找不到有效的Redis缓存');
        return Command::FAILURE;
    }

    private function clearTags(RedisTagAwareAdapter $adapter, OutputInterface $output): void
    {
        $reflection = new \ReflectionClass(RedisTagAwareAdapter::class);

        $namespace = $reflection->getProperty('namespace')->getValue($adapter);
        /** @var \Redis $redis */
        $redis = $reflection->getProperty('redis')->getValue($adapter);

        $tagKeyPattern = $namespace. ':' . TagAwareAdapter::TAGS_PREFIX . '*';

        // 使用 SCAN 命令逐个处理符合模式的键，避免一次性加载所有键到内存
        $cursor = null;
        do {
            $keys = $redis->scan($cursor, $tagKeyPattern, 100);
            if ($keys !== false) {
                // 立即处理这批标签键
                foreach ($keys as $tagKey) {
                    $this->processTagKey($redis, $tagKey, $output);
                }
            }
        } while ($cursor !== 0 && $cursor !== false);
    }

    /**
     * 处理单个标签键
     */
    private function processTagKey(\Redis $redis, string $tagKey, OutputInterface $output): void
    {
        $cursorTag = null;
        $batchSize = 100;

        do {
            // 获取标签下的部分缓存键
            $members = $redis->sscan($tagKey, $cursorTag, null, $batchSize);
            if ($members === false) {
                continue;
            }

            if (empty($members)) {
                continue;
            }

            $keysToRemove = [];

            // 使用管道批量检查键是否存在
            $redis->multi(\Redis::PIPELINE);
            foreach ($members as $cacheKey) {
                $redis->exists($cacheKey);
            }
            $existsResults = $redis->exec();

            foreach ($members as $index => $cacheKey) {
                if (!$existsResults[$index]) {
                    $keysToRemove[] = $cacheKey;
                }
            }

            if (!empty($keysToRemove)) {
                // 使用管道批量移除无效的缓存键
                $output->writeln(sprintf('清理标签：%s => %s', $tagKey, implode(', ', $keysToRemove)));
                $redis->multi(\Redis::PIPELINE);
                foreach ($keysToRemove as $invalidKey) {
                    $redis->srem($tagKey, $invalidKey);
                }
                $redis->exec();
            }
        } while ($cursorTag !== 0 && $cursorTag !== false);
    }
}
