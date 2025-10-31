<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\Symfony\AopCacheBundle\Attribute\Cacheble;

/**
 * @internal
 */
#[CoversClass(Cacheble::class)]
final class CachebleTest extends TestCase
{
    public function testCachebleDefaultValues(): void
    {
        $cacheble = new Cacheble();

        $this->assertNull($cacheble->key);
        $this->assertNull($cacheble->ttl);
        $this->assertEmpty($cacheble->tags);
    }

    public function testCachebleCustomValues(): void
    {
        $key = 'test_key';
        $ttl = 3600;
        $tags = ['tag1', 'tag2'];

        $cacheble = new Cacheble($key, $ttl, $tags);

        $this->assertSame($key, $cacheble->key);
        $this->assertSame($ttl, $cacheble->ttl);
        $this->assertSame($tags, $cacheble->tags);
    }
}
