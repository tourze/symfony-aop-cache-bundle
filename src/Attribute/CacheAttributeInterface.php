<?php

namespace Tourze\Symfony\AopCacheBundle\Attribute;

interface CacheAttributeInterface
{
    public function getKey(): ?string;
    public function getTtl(): ?int;
    public function getTags(): ?array;
}