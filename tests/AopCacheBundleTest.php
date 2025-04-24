<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tourze\Symfony\AopCacheBundle\AopCacheBundle;
use Tourze\Symfony\AopCacheBundle\DependencyInjection\CacheCompilerPass;

class AopCacheBundleTest extends TestCase
{
    public function testBundleClass(): void
    {
        $bundle = new AopCacheBundle();

        // 验证 Bundle 类是否正确继承
        $this->assertInstanceOf(\Symfony\Component\HttpKernel\Bundle\Bundle::class, $bundle);

        // 验证编译通过类是否正确
        $reflection = new ReflectionClass(CacheCompilerPass::class);
        $this->assertTrue($reflection->implementsInterface(\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::class));
    }

    public function testBootMethod(): void
    {
        $bundle = new AopCacheBundle();

        // 由于 boot 方法不返回值，仅验证不会抛出异常
        try {
            $bundle->boot();
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            $this->fail('Boot method should not throw an exception');
        }
    }
}
