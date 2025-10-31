<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Aspect;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\AopCacheBundle\Aspect\CachebleAspect;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @internal
 */
#[CoversClass(CachebleAspect::class)]
final class CachebleAspectTest extends TestCase
{
    private CachebleAspect $aspect;

    private CacheInterface $cache;

    private CacheItemPoolInterface $cacheItemPool;

    /**
     * @var JoinPoint&MockObject
     */
    private MockObject $joinPoint;

    /**
     * @var JoinPoint&MockObject
     */
    private MockObject $regularJoinPoint;

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
        $this->aspect = new CachebleAspect($this->cache, $this->cacheItemPool, $twig);

        // 创建带注解方法的 JoinPoint
        $testService = new CachebleTestServiceMock();
        // 使用具体类 JoinPoint 进行 mock 是必要的，因为：
        // 1. JoinPoint 类包含了 AOP 框架的核心状态和行为
        // 2. 该类的实例在运行时由 AOP 框架动态创建，测试中需要模拟其完整行为
        // 3. 没有合适的接口或抽象类可以替代，且需要访问其特定的方法实现
        $this->joinPoint = $this->createMock(JoinPoint::class);
        $this->joinPoint->method('getInstance')->willReturn($testService);
        $this->joinPoint->method('getMethod')->willReturn('testMethod');
        $this->joinPoint->method('getParams')->willReturn(['param' => 'test_value']);
        $this->joinPoint->method('getReturnValue')->willReturn('Result for: test_value');
        $this->joinPoint->method('getUniqueId')->willReturn('CachebleTestService.testMethod.test_value');
        $this->joinPoint->method('isReturnEarly')->willReturn(false);

        // 创建不带注解方法的 JoinPoint
        // 使用具体类 JoinPoint 进行 mock 是必要的，因为：
        // 1. JoinPoint 类包含了 AOP 框架的核心状态和行为
        // 2. 该类的实例在运行时由 AOP 框架动态创建，测试中需要模拟其完整行为
        // 3. 没有合适的接口或抽象类可以替代，且需要访问其特定的方法实现
        $this->regularJoinPoint = $this->createMock(JoinPoint::class);
        $this->regularJoinPoint->method('getInstance')->willReturn($testService);
        $this->regularJoinPoint->method('getMethod')->willReturn('regularMethod');
        $this->regularJoinPoint->method('isReturnEarly')->willReturn(false);
    }

    public function testFindByCacheWithoutCache(): void
    {
        // 测试缓存未命中的情况
        $this->aspect->findByCache($this->joinPoint);
        $this->assertFalse($this->joinPoint->isReturnEarly());
    }

    public function testFindByCacheWithCache(): void
    {
        // 先存入缓存
        $this->cacheItemPool->getItem('cache_test_test_value')->set('Cached result');

        // 创建一个新的 mock，模拟缓存命中的情况
        $testService = new CachebleTestServiceMock();
        $cachedJoinPoint = $this->createMock(JoinPoint::class);
        $cachedJoinPoint->method('getInstance')->willReturn($testService);
        $cachedJoinPoint->method('getMethod')->willReturn('testMethod');
        $cachedJoinPoint->method('getParams')->willReturn(['param' => 'test_value']);
        $cachedJoinPoint->method('getReturnValue')->willReturn('Cached result');
        $cachedJoinPoint->method('getUniqueId')->willReturn('CachebleTestService.testMethod.test_value');
        $cachedJoinPoint->method('isReturnEarly')->willReturn(true);

        // 测试缓存命中的情况
        $this->aspect->findByCache($cachedJoinPoint);
        $this->assertTrue($cachedJoinPoint->isReturnEarly());
        $this->assertEquals('Cached result', $cachedJoinPoint->getReturnValue());
    }

    public function testFindByCacheWithoutAttribute(): void
    {
        // 测试没有注解的方法，不应该从缓存中查找
        $this->aspect->findByCache($this->regularJoinPoint);
        $this->assertFalse($this->regularJoinPoint->isReturnEarly());
    }

    public function testSaveCache(): void
    {
        // 清理缓存确保测试环境干净
        $this->cacheItemPool->clear();

        // 执行缓存保存
        $this->aspect->saveCache($this->joinPoint);

        // 验证缓存中已存在数据
        $cacheItem = $this->cacheItemPool->getItem('cache_test_test_value');
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
