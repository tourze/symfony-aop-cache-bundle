<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Tourze\Symfony\AopCacheBundle\DependencyInjection\CacheCompilerPass;

/**
 * @internal
 */
#[CoversClass(CacheCompilerPass::class)]
final class CacheCompilerPassTest extends TestCase
{
    public function testProcessWithCacheApp(): void
    {
        $container = new ContainerBuilder();
        $cacheDefinition = new Definition();
        $container->setDefinition('cache.app', $cacheDefinition);

        $compilerPass = new CacheCompilerPass();
        $compilerPass->process($container);

        $this->assertTrue($cacheDefinition->isLazy());
    }

    public function testProcessWithoutCacheApp(): void
    {
        $container = new ContainerBuilder();

        $compilerPass = new CacheCompilerPass();
        $compilerPass->process($container);

        // 不应抛出异常，仅验证过程完成
        $this->expectNotToPerformAssertions();
    }
}
