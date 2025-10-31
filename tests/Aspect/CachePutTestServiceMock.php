<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Aspect;

use Tourze\Symfony\AopCacheBundle\Attribute\CachePut;

class CachePutTestServiceMock
{
    #[CachePut(key: 'test_{{ param }}', ttl: 3600)]
    public function testMethod(string $param): string
    {
        return 'Result for: ' . $param;
    }

    public function regularMethod(string $param): string
    {
        return 'Regular result: ' . $param;
    }
}
