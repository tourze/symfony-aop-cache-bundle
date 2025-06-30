<?php

namespace Tourze\Symfony\AopCacheBundle\Attribute;

/**
 * 用于配置方法的结果应该被放入缓存，无论该方法的调用者是否从缓存中获取结果。
 * 这个注解依赖AOP
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class CachePut implements CacheAttributeInterface
{
    public function __construct(
        public ?string $key = null,
        public ?int $ttl = null,
    )
    {
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function getTags(): ?array
    {
        return [];
    }
}
