<?php

namespace Tourze\Symfony\AopCacheBundle\Attribute;

/**
 * 添加这个注解到方法上，可以快速为这个方法添加上缓存读写逻辑。
 * 要注意的是，这个方法对返回值有要求，部分对象/闭包函数的缓存逻辑会被跳过
 * 这个注解依赖AOP
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheble
{
    public function __construct(
        public ?string $key = null,
        public ?int $ttl = null,
        public ?array $tags = [],
    )
    {
    }
}
