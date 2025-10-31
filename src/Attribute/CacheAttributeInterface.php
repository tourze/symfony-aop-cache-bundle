<?php

namespace Tourze\Symfony\AopCacheBundle\Attribute;

interface CacheAttributeInterface
{
    public function getKey(): ?string;

    public function getTtl(): ?int;

    /**
     * @return array<string>|null
     */
    public function getTags(): ?array;
}
