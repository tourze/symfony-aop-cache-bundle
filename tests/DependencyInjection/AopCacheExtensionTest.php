<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\Symfony\AopCacheBundle\DependencyInjection\AopCacheExtension;

class AopCacheExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $extension = new AopCacheExtension();
        $container = new ContainerBuilder();

        // 不应该抛出异常
        try {
            $extension->load([], $container);
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            $this->fail('Extension load method should not throw exception: ' . $e->getMessage());
        }

        // 验证服务定义是否正确加载
        $this->assertTrue($container->hasDefinition('Tourze\Symfony\AopCacheBundle\Aspect\CachebleAspect') ||
            $container->hasDefinition('tourze.symfony.aop_cache_bundle.aspect.cacheble_aspect'));
    }
}
