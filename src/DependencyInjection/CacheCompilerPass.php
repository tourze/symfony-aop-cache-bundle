<?php

namespace Tourze\Symfony\AopCacheBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CacheCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('cache.app')) {
            $container->getDefinition('cache.app')->setLazy(true);
        }
    }
}
