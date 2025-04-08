<?php

namespace Tourze\Symfony\AopCacheBundle\Aspect;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Tourze\Symfony\Aop\Attribute\AfterReturning;
use Tourze\Symfony\Aop\Attribute\Aspect;
use Tourze\Symfony\Aop\Attribute\Before;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\AopCacheBundle\Attribute\Cacheble;
use Twig\Environment;

/**
 * 提供快速的缓存能力，开发者通过使用注解即可为指定方法快速开启缓存读写
 */
#[Aspect]
class CachebleAspect
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly CacheItemPoolInterface $cacheItemPool,
        private readonly Environment $twig,
    )
    {
    }

    use CacheTrait;

    /**
     * 在开始执行前，我们加一层缓存。如果能查找到的话，我们就直接返回不继续执行方法了
     */
    #[Before(methodAttribute: Cacheble::class)]
    public function findByCache(JoinPoint $joinPoint): void
    {
        $key = $this->buildKey($joinPoint);
        if ($key === null) {
            return;
        }

        // 因为不能确定函数返回值的类型，如果调用多一次has，又可能会有事务问题，所以这里生成一个随机数来进行对比
        $cacheItem = $this->cacheItemPool->getItem($key);
        if (!$cacheItem->isHit()) {
            // 这里意味着没读到缓存
            return;
        }

        // 返回结果，不继续执行了
        $joinPoint->setReturnEarly(true);
        $joinPoint->setReturnValue($cacheItem->get());
    }

    /**
     * 执行成功后，我们保存结果到缓存去
     */
    #[AfterReturning(methodAttribute: Cacheble::class)]
    public function saveCache(JoinPoint $joinPoint): void
    {
        $key = $this->buildKey($joinPoint);
        if ($key === null) {
            return;
        }

        $this->persistCache($joinPoint, $key);
    }
}
