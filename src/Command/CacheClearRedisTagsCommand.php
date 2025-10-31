<?php

namespace Tourze\Symfony\AopCacheBundle\Command;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\Adapter\TraceableAdapter;
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
        private readonly AdapterInterface $cache,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adapter = $this->unwrapAdapter($this->cache);

        if ($adapter instanceof RedisTagAwareAdapter) {
            $this->clearTags($adapter, $output);

            return Command::SUCCESS;
        }

        $output->writeln('找不到有效的Redis缓存');

        return Command::FAILURE;
    }

    /**
     * 解包适配器以获取实际的 RedisTagAwareAdapter
     */
    private function unwrapAdapter(AdapterInterface $adapter): AdapterInterface
    {
        $result = $adapter;

        if ($result instanceof TraceableAdapter) {
            // TraceableAdapter and TraceableTagAwareAdapter wrap the actual pool
            // We need to access the decorated adapter through reflection
            $reflection = new \ReflectionClass($result);
            $property = $reflection->getProperty('pool');
            $property->setAccessible(true);
            $result = $property->getValue($result);
        }

        if ($result instanceof HotkeySmartCache) {
            $result = $result->getDecorated();
        }

        if ($result instanceof LazyObjectInterface) {
            $result->initializeLazyObject();
        }

        return $result;
    }

    private function clearTags(RedisTagAwareAdapter $adapter, OutputInterface $output): void
    {
        $reflection = new \ReflectionClass(RedisTagAwareAdapter::class);

        $namespace = $reflection->getProperty('namespace')->getValue($adapter);
        $redis = $reflection->getProperty('redis')->getValue($adapter);
        assert($redis instanceof \Redis);

        $tagKeyPattern = $namespace . ':' . TagAwareAdapter::TAGS_PREFIX . '*';

        // 使用 SCAN 命令逐个处理符合模式的键，避免一次性加载所有键到内存
        $cursor = null;
        do {
            $keys = $redis->scan($cursor, $tagKeyPattern, 100);
            if (false !== $keys) {
                // 立即处理这批标签键
                foreach ($keys as $tagKey) {
                    $this->processTagKey($redis, $tagKey, $output);
                }
            }
        } while (null !== $cursor && 0 !== $cursor);
    }

    /**
     * 处理单个标签键
     */
    private function processTagKey(\Redis $redis, string $tagKey, OutputInterface $output): void
    {
        $cursorTag = null;
        $batchSize = 100;

        do {
            // 直接在这里处理 Redis scan，避免引用参数问题
            $members = $redis->sscan($tagKey, $cursorTag, null, $batchSize);
            if (!is_array($members) || count($members) === 0) {
                continue;
            }

            $keysToRemove = $this->findInvalidKeys($redis, $members);
            $this->removeInvalidKeys($redis, $tagKey, $keysToRemove, $output);
        } while (null !== $cursorTag && 0 !== $cursorTag);
    }

    /**
     * 查找无效的缓存键
     */
    /**
     * @param array<string> $members
     * @return array<string>
     */
    private function findInvalidKeys(\Redis $redis, array $members): array
    {
        $redis->multi(\Redis::PIPELINE);
        foreach ($members as $cacheKey) {
            $redis->exists($cacheKey);
        }
        $existsResults = $redis->exec();

        $keysToRemove = [];
        foreach ($members as $index => $cacheKey) {
            if (!$existsResults[$index]) {
                $keysToRemove[] = $cacheKey;
            }
        }

        return $keysToRemove;
    }

    /**
     * 移除无效的缓存键
     */
    /**
     * @param array<string> $keysToRemove
     */
    private function removeInvalidKeys(\Redis $redis, string $tagKey, array $keysToRemove, OutputInterface $output): void
    {
        if (count($keysToRemove) === 0) {
            return;
        }

        $output->writeln(sprintf('清理标签：%s => %s', $tagKey, implode(', ', $keysToRemove)));

        $redis->multi(\Redis::PIPELINE);
        foreach ($keysToRemove as $invalidKey) {
            $redis->srem($tagKey, $invalidKey);
        }
        $redis->exec();
    }
}
