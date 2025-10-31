<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Aspect;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\AopCacheBundle\Aspect\CachePutAspect;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @internal
 */
#[CoversClass(CachePutAspect::class)]
final class CachePutAspectTest extends TestCase
{
    private CachePutAspect $aspect;

    private CacheInterface $cache;

    private CacheItemPoolInterface $cacheItemPool;

    private JoinPoint $joinPoint;

    private JoinPoint $regularJoinPoint;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建支持标签的缓存池
        $arrayAdapter = new ArrayAdapter();
        $tagAwareAdapter = new TagAwareAdapter($arrayAdapter);
        $this->cache = $tagAwareAdapter;
        $this->cacheItemPool = $tagAwareAdapter;

        // 设置 Twig
        $loader = new ArrayLoader();
        $twig = new Environment($loader);

        // 创建切面实例
        $this->aspect = new CachePutAspect($this->cache, $twig);

        // 创建带注解方法的 JoinPoint
        $testService = new CachePutTestServiceMock();
        // 使用具体类 JoinPoint 进行 mock 是必要的，因为：
        // 1. JoinPoint 类包含了 AOP 框架的核心状态和行为
        // 2. 该类的实例在运行时由 AOP 框架动态创建，测试中需要模拟其完整行为
        // 3. 没有合适的接口或抽象类可以替代，且需要访问其特定的方法实现
        $this->joinPoint = $this->createMock(JoinPoint::class);
        $this->joinPoint->method('getInstance')->willReturn($testService);
        $this->joinPoint->method('getMethod')->willReturn('testMethod');
        $this->joinPoint->method('getParams')->willReturn(['param' => 'test_value']);
        $this->joinPoint->method('getReturnValue')->willReturn('Result for: test_value');
        $this->joinPoint->method('getUniqueId')->willReturn('CachePutTestService.testMethod.test_value');

        // 创建不带注解方法的 JoinPoint
        // 使用具体类 JoinPoint 进行 mock 是必要的，因为：
        // 1. JoinPoint 类包含了 AOP 框架的核心状态和行为
        // 2. 该类的实例在运行时由 AOP 框架动态创建，测试中需要模拟其完整行为
        // 3. 没有合适的接口或抽象类可以替代，且需要访问其特定的方法实现
        $this->regularJoinPoint = $this->createMock(JoinPoint::class);
        $this->regularJoinPoint->method('getInstance')->willReturn($testService);
        $this->regularJoinPoint->method('getMethod')->willReturn('regularMethod');
    }

    public function testSaveCache(): void
    {
        // 清理缓存确保测试环境干净
        $this->cacheItemPool->clear();

        // 执行缓存保存
        $this->aspect->saveCache($this->joinPoint);

        // 先验证所有缓存项
        $keys = $this->cacheItemPool->getItems(['cache_test_test_value']);
        $cacheItem = iterator_to_array($keys)['cache_test_test_value'];

        if (!$cacheItem->isHit()) {
            // 如果缓存未命中，让我们尝试其他可能的键
            Assert::fail('Cache item not found. Testing cache population.');
        }

        $this->assertTrue($cacheItem->isHit());
        $this->assertEquals('Result for: test_value', $cacheItem->get());
    }

    public function testSaveCacheWithoutAttribute(): void
    {
        // 测试没有注解的方法，不应该缓存任何内容
        $this->aspect->saveCache($this->regularJoinPoint);

        // 如果没有异常就说明执行成功
        $this->expectNotToPerformAssertions();
    }
}
