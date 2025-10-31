<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Aspect;

use Symfony\Contracts\Cache\CacheInterface;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\AopCacheBundle\Aspect\CacheTrait;
use Tourze\Symfony\AopCacheBundle\Attribute\CacheAttributeInterface;
use Twig\Environment;

class CacheTraitTestClass
{
    use CacheTrait;

    private readonly CacheInterface $cache;

    private readonly Environment $twig;

    public function __construct(
        CacheInterface $cache,
        Environment $twig,
    ) {
        $this->cache = $cache;
        $this->twig = $twig;
    }

    public function exposeGetAttribute(JoinPoint $joinPoint): ?CacheAttributeInterface
    {
        return $this->getAttribute($joinPoint);
    }

    public function exposeBuildKey(JoinPoint $joinPoint): ?string
    {
        return $this->buildKey($joinPoint);
    }

    public function exposeGetTTL(JoinPoint $joinPoint): ?int
    {
        return $this->getTTL($joinPoint);
    }

    /**
     * @return array<string>|null
     */
    public function exposeGetTags(JoinPoint $joinPoint): ?array
    {
        return $this->getTags($joinPoint);
    }

    public function exposeCouldResultSave(mixed $var): bool
    {
        return $this->couldResultSave($var);
    }

    public function exposePersistCache(JoinPoint $joinPoint, string $key): void
    {
        $this->persistCache($joinPoint, $key);
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
