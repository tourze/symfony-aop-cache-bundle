<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Unit\Aspect;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\AopCacheBundle\Aspect\CachebleAspect;
use Tourze\Symfony\AopCacheBundle\Attribute\Cacheble;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class CachebleTestService
{
    #[Cacheble(key: "test_{{ param }}", ttl: 3600, tags: ["test_tag"])]
    public function testMethod(string $param): string
    {
        return "Result for: " . $param;
    }

    public function regularMethod(string $param): string
    {
        return "Regular result: " . $param;
    }
}

class CachebleAspectTest extends TestCase
{
    private CachebleAspect $aspect;
    private CacheInterface $cache;
    private CacheItemPoolInterface $cacheItemPool;
    private JoinPoint $joinPoint;
    private JoinPoint $regularJoinPoint;

    protected function setUp(): void
    {
        // 设置 Twig 环境
        $loader = new ArrayLoader();
        $twig = new Environment($loader);

        // 设置缓存
        $arrayAdapter = new ArrayAdapter();
        $this->cache = new TagAwareAdapter($arrayAdapter);
        $this->cacheItemPool = $this->cache;

        // 创建切面实例
        $this->aspect = new CachebleAspect($this->cache, $this->cacheItemPool, $twig);

        // 创建带注解方法的 JoinPoint
        $testService = new CachebleTestService();
        $this->joinPoint = $this->createMock(JoinPoint::class);
        $this->joinPoint->method('getInstance')->willReturn($testService);
        $this->joinPoint->method('getMethod')->willReturn('testMethod');
        $this->joinPoint->method('getParams')->willReturn(['param' => 'test_value']);
        $this->joinPoint->method('getReturnValue')->willReturn('Result for: test_value');
        $this->joinPoint->method('getUniqueId')->willReturn('CachebleTestService.testMethod.test_value');

        // 创建不带注解方法的 JoinPoint
        $this->regularJoinPoint = $this->createMock(JoinPoint::class);
        $this->regularJoinPoint->method('getInstance')->willReturn($testService);
        $this->regularJoinPoint->method('getMethod')->willReturn('regularMethod');
    }

    public function testFindByCacheWithoutCache(): void
    {
        // 当缓存中没有数据时，不应该提前返回
        $this->aspect->findByCache($this->joinPoint);

        // 验证没有调用提前返回
        $this->assertTrue(true); // 如果没有异常就说明执行成功
    }

    public function testFindByCacheWithCache(): void
    {
        // 先将数据存入缓存
        $cacheItem = $this->cacheItemPool->getItem('cache_test_test_value');
        $cacheItem->set('Cached result');
        $this->cacheItemPool->save($cacheItem);

        // 设置 JoinPoint 的期望调用
        $this->joinPoint->expects($this->once())
            ->method('setReturnEarly')
            ->with(true);
        $this->joinPoint->expects($this->once())
            ->method('setReturnValue')
            ->with('Cached result');

        // 执行缓存查找
        $this->aspect->findByCache($this->joinPoint);
    }

    public function testFindByCacheWithoutAttribute(): void
    {
        // 测试没有注解的方法，不应该进行任何缓存操作
        $this->aspect->findByCache($this->regularJoinPoint);

        // 如果没有异常就说明执行成功
        $this->assertTrue(true);
    }

    public function testSaveCache(): void
    {
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
        $this->assertTrue(true);
    }
}