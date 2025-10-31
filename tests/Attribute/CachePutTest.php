<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\Symfony\AopCacheBundle\Attribute\CachePut;

/**
 * @internal
 */
#[CoversClass(CachePut::class)]
final class CachePutTest extends TestCase
{
    public function testCachePutDefaultValues(): void
    {
        $cachePut = new CachePut();

        $this->assertNull($cachePut->key);
        $this->assertNull($cachePut->ttl);
    }

    public function testCachePutCustomValues(): void
    {
        $key = 'test_key';
        $ttl = 3600;

        $cachePut = new CachePut($key, $ttl);

        $this->assertSame($key, $cachePut->key);
        $this->assertSame($ttl, $cachePut->ttl);
    }
}
