<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Aspect;

use Tourze\Symfony\AopCacheBundle\Attribute\Cacheble;

class CacheTestServiceMock
{
    #[Cacheble(key: 'test_{{ param }}', ttl: 3600, tags: ['test_tag', 'user_{{ param }}'])]
    public function testMethod(string $param): string
    {
        return 'Result for: ' . $param;
    }

    public function regularMethod(string $param): string
    {
        return 'Regular result: ' . $param;
    }
}
