<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Aspect;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Tourze\Symfony\AopCacheBundle\Aspect\CachebleAspect;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @internal
 */
#[CoversClass(CachebleAspect::class)]
final class CachebleAspectInstantiationTest extends TestCase
{
    private ArrayAdapter $arrayAdapter;

    private TagAwareAdapter $cache;

    private Environment $twig;

    protected function setUp(): void
    {
        parent::setUp();

        $loader = new ArrayLoader([
            'test.twig' => 'Hello {{ name }}!',
        ]);
        $this->twig = new Environment($loader);

        $this->arrayAdapter = new ArrayAdapter();
        $this->cache = new TagAwareAdapter($this->arrayAdapter);
    }

    public function testCachebleAspectInstantiation(): void
    {
        $aspect = new CachebleAspect($this->cache, $this->cache, $this->twig);
        $this->assertInstanceOf(CachebleAspect::class, $aspect);
    }

    public function testFindByCacheMethod(): void
    {
        $aspect = new CachebleAspect($this->cache, $this->cache, $this->twig);
        $reflection = new \ReflectionClass(CachebleAspect::class);
        $this->assertTrue($reflection->hasMethod('findByCache'));
    }

    public function testSaveCacheMethod(): void
    {
        $aspect = new CachebleAspect($this->cache, $this->cache, $this->twig);
        $reflection = new \ReflectionClass(CachebleAspect::class);
        $this->assertTrue($reflection->hasMethod('saveCache'));
    }
}
