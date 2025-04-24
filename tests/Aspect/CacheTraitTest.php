<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Aspect;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\AopCacheBundle\Aspect\CacheTrait;
use Tourze\Symfony\AopCacheBundle\Attribute\Cacheble;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class CacheTraitTestClass
{
    use CacheTrait;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Environment    $twig,
    )
    {
    }

    // 暴露特性中的方法以便测试
    public function exposeGetAttribute(JoinPoint $joinPoint): ?Cacheble
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
}

class CacheTestService
{
    #[Cacheble(key: "test_{{ param }}", ttl: 3600, tags: ["test_tag", "user_{{ param }}"])]
    public function testMethod(string $param): string
    {
        return "Result for: " . $param;
    }

    public function regularMethod(string $param): string
    {
        return "Regular result: " . $param;
    }
}

class CacheTraitTest extends TestCase
{
    private CacheTraitTestClass $traitInstance;
    private JoinPoint $joinPoint;
    private JoinPoint $regularJoinPoint;

    protected function setUp(): void
    {
        // 设置 Twig 环境
        $loader = new ArrayLoader();
        $twig = new Environment($loader);

        // 设置缓存
        $arrayAdapter = new ArrayAdapter();
        $cache = new TagAwareAdapter($arrayAdapter);

        // 创建测试类实例
        $this->traitInstance = new CacheTraitTestClass($cache, $twig);

        // 创建带注解方法的 JoinPoint
        $testService = new CacheTestService();
        $this->joinPoint = $this->createMock(JoinPoint::class);
        $this->joinPoint->method('getInstance')->willReturn($testService);
        $this->joinPoint->method('getMethod')->willReturn('testMethod');
        $this->joinPoint->method('getParams')->willReturn(['param' => 'test_value']);
        $this->joinPoint->method('getReturnValue')->willReturn('Result for: test_value');
        $this->joinPoint->method('getUniqueId')->willReturn('CacheTestService.testMethod.test_value');

        // 创建不带注解方法的 JoinPoint
        $this->regularJoinPoint = $this->createMock(JoinPoint::class);
        $this->regularJoinPoint->method('getInstance')->willReturn($testService);
        $this->regularJoinPoint->method('getMethod')->willReturn('regularMethod');
    }

    public function testGetAttribute(): void
    {
        // 测试带 Cacheble 注解的方法
        $attribute = $this->traitInstance->exposeGetAttribute($this->joinPoint);
        $this->assertInstanceOf(Cacheble::class, $attribute);

        // 测试不带注解的方法
        $attribute = $this->traitInstance->exposeGetAttribute($this->regularJoinPoint);
        $this->assertNull($attribute);
    }

    public function testBuildKey(): void
    {
        $key = $this->traitInstance->exposeBuildKey($this->joinPoint);
        $this->assertNotNull($key);
        $this->assertStringContainsString('cache_test_test_value', $key);

        // 测试没有注解的情况
        $key = $this->traitInstance->exposeBuildKey($this->regularJoinPoint);
        $this->assertNull($key);
    }

    public function testGetTTL(): void
    {
        $ttl = $this->traitInstance->exposeGetTTL($this->joinPoint);
        $this->assertEquals(3600, $ttl);

        // 测试默认值
        $ttl = $this->traitInstance->exposeGetTTL($this->regularJoinPoint);
        $this->assertEquals(60, $ttl);
    }

    public function testGetTags(): void
    {
        $tags = $this->traitInstance->exposeGetTags($this->joinPoint);
        $this->assertIsArray($tags);
        $this->assertContains('test_tag', $tags);
        $this->assertContains('user_test_value', $tags);

        // 测试没有注解的情况
        $tags = $this->traitInstance->exposeGetTags($this->regularJoinPoint);
        $this->assertNull($tags);
    }

    public function testCouldResultSave(): void
    {
        // 测试可以缓存的类型
        $this->assertTrue($this->traitInstance->exposeCouldResultSave('string'));
        $this->assertTrue($this->traitInstance->exposeCouldResultSave(123));
        $this->assertTrue($this->traitInstance->exposeCouldResultSave(['array']));

        // 测试不可缓存的类型
        $resource = fopen('php://memory', 'r');
        $this->assertFalse($this->traitInstance->exposeCouldResultSave($resource));
        fclose($resource);

        $this->assertFalse($this->traitInstance->exposeCouldResultSave(function () {
        }));
    }
}
