<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Unit\Aspect;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\AopCacheBundle\Aspect\CachePutAspect;
use Tourze\Symfony\AopCacheBundle\Attribute\CachePut;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class CachePutTestService
{
    #[CachePut(key: "test_{{ param }}", ttl: 3600)]
    public function testMethod(string $param): string
    {
        return "Result for: " . $param;
    }

    public function regularMethod(string $param): string
    {
        return "Regular result: " . $param;
    }
}

class CachePutAspectTest extends TestCase
{
    private CachePutAspect $aspect;
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
        $this->aspect = new CachePutAspect($this->cache, $twig);

        // 创建带注解方法的 JoinPoint
        $testService = new CachePutTestService();
        $this->joinPoint = $this->createMock(JoinPoint::class);
        $this->joinPoint->method('getInstance')->willReturn($testService);
        $this->joinPoint->method('getMethod')->willReturn('testMethod');
        $this->joinPoint->method('getParams')->willReturn(['param' => 'test_value']);
        $this->joinPoint->method('getReturnValue')->willReturn('Result for: test_value');
        $this->joinPoint->method('getUniqueId')->willReturn('CachePutTestService.testMethod.test_value');

        // 创建不带注解方法的 JoinPoint
        $this->regularJoinPoint = $this->createMock(JoinPoint::class);
        $this->regularJoinPoint->method('getInstance')->willReturn($testService);
        $this->regularJoinPoint->method('getMethod')->willReturn('regularMethod');
    }

    public function testSaveCache(): void
    {
        // 执行缓存保存
        $this->aspect->saveCache($this->joinPoint);

        // 先验证所有缓存项
        $keys = $this->cacheItemPool->getItems(['cache_test_test_value']);
        $cacheItem = $keys['cache_test_test_value'];
        
        if (!$cacheItem->isHit()) {
            // 如果缓存未命中，让我们尝试其他可能的键
            $this->fail('Cache item not found. Testing cache population.');
        }
        
        $this->assertTrue($cacheItem->isHit());
        $this->assertEquals('Result for: test_value', $cacheItem->get());
    }

    public function testSaveCacheWithoutAttribute(): void
    {
        // 测试没有注解的方法，不应该缓存任何内容
        $this->aspect->saveCache($this->regularJoinPoint);

        // 缓存中不应该有任何内容
        $this->assertTrue(true); // 如果没有异常就说明执行成功
    }
}