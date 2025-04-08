<?php

namespace Tourze\Symfony\AopCacheBundle\Aspect;

use Symfony\Contracts\Cache\CacheInterface;
use Tourze\Symfony\Aop\Attribute\AfterReturning;
use Tourze\Symfony\Aop\Attribute\Aspect;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\AopCacheBundle\Attribute\CachePut;
use Twig\Environment;

/**
 * 用于配置方法的结果应该被放入缓存，无论该方法的调用者是否从缓存中获取结果。
 */
#[Aspect]
class CachePutAspect
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Environment $twig,
    )
    {
    }

    use CacheTrait;

    /**
     * 执行成功后，我们保存结果到缓存去
     */
    #[AfterReturning(methodAttribute: CachePut::class)]
    public function saveCache(JoinPoint $joinPoint): void
    {
        $key = $this->buildKey($joinPoint);
        if ($key === null) {
            return;
        }

        $this->persistCache($joinPoint, $key);
    }
}
