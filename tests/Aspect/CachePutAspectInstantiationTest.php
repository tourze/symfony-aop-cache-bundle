<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Aspect;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Tourze\Symfony\AopCacheBundle\Aspect\CachePutAspect;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @internal
 */
#[CoversClass(CachePutAspect::class)]
final class CachePutAspectInstantiationTest extends TestCase
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

    public function testCachePutAspectInstantiation(): void
    {
        $aspect = new CachePutAspect($this->cache, $this->twig);
        $this->assertInstanceOf(CachePutAspect::class, $aspect);
    }

    public function testSaveCacheMethod(): void
    {
        $aspect = new CachePutAspect($this->cache, $this->twig);
        $reflection = new \ReflectionClass(CachePutAspect::class);
        $this->assertTrue($reflection->hasMethod('saveCache'));
    }
}
