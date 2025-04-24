<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Aspect;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Tourze\Symfony\AopCacheBundle\Aspect\CachebleAspect;
use Tourze\Symfony\AopCacheBundle\Aspect\CachePutAspect;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class CacheAspectTest extends TestCase
{
    private ArrayAdapter $arrayAdapter;
    private TagAwareAdapter $cache;
    private Environment $twig;

    protected function setUp(): void
    {
        // 设置 Twig 环境
        $loader = new ArrayLoader([
            'test.twig' => 'Hello {{ name }}!',
        ]);
        $this->twig = new Environment($loader);

        // 设置缓存
        $this->arrayAdapter = new ArrayAdapter();
        $this->cache = new TagAwareAdapter($this->arrayAdapter);
    }

    public function testCachebleAspectInstantiation(): void
    {
        $aspect = new CachebleAspect($this->cache, $this->cache, $this->twig);
        $this->assertInstanceOf(CachebleAspect::class, $aspect);
    }

    public function testCachePutAspectInstantiation(): void
    {
        $aspect = new CachePutAspect($this->cache, $this->twig);
        $this->assertInstanceOf(CachePutAspect::class, $aspect);
    }

    public function testCachebleTrait(): void
    {
        // 测试特性的基本行为
        $reflection = new \ReflectionClass(CachebleAspect::class);
        $this->assertTrue($reflection->hasMethod('findByCache'));
        $this->assertTrue($reflection->hasMethod('saveCache'));
    }

    public function testCachePutTrait(): void
    {
        // 测试特性的基本行为
        $reflection = new \ReflectionClass(CachePutAspect::class);
        $this->assertTrue($reflection->hasMethod('saveCache'));
    }
}
