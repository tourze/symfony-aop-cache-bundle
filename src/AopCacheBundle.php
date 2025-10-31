<?php

namespace Tourze\Symfony\AopCacheBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BacktraceHelper\Backtrace;
use Tourze\Symfony\AopCacheBundle\DependencyInjection\CacheCompilerPass;
use Tourze\Symfony\CacheHotKey\Service\HotkeySmartCache;

class AopCacheBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        // 缓存的额外处理
        parent::build($container);
        $container->addCompilerPass(new CacheCompilerPass());
    }

    public function boot(): void
    {
        parent::boot();

        $fileName = (new \ReflectionClass(HotkeySmartCache::class))->getFileName();
        if (false !== $fileName) {
            Backtrace::addProdIgnoreFiles($fileName);
        }
    }
}
