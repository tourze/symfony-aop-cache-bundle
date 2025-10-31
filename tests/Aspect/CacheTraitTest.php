<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Aspect;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Tourze\Symfony\Aop\Model\JoinPoint;
use Tourze\Symfony\AopCacheBundle\Aspect\CacheTrait;
use Tourze\Symfony\AopCacheBundle\Attribute\CacheAttributeInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @internal
 */
#[CoversClass(CacheTrait::class)]
final class CacheTraitTest extends TestCase
{
    private CacheTraitTestClass $traitInstance;

    private JoinPoint $joinPoint;

    private JoinPoint $regularJoinPoint;

    protected function setUp(): void
    {
        parent::setUp();

        // 设置 Twig 环境
        $loader = new ArrayLoader();
        $twig = new Environment($loader);

        // 设置缓存
        $arrayAdapter = new ArrayAdapter();
        $cache = new TagAwareAdapter($arrayAdapter);

        // 创建测试类实例
        $this->traitInstance = new CacheTraitTestClass($cache, $twig);

        // 创建带注解方法的 JoinPoint
        $testService = new CacheTestServiceMock();
        // 使用具体类 JoinPoint 进行 mock 是必要的，因为：
        // 1. JoinPoint 类包含了 AOP 框架的核心状态和行为
        // 2. 该类的实例在运行时由 AOP 框架动态创建，测试中需要模拟其完整行为
        // 3. 没有合适的接口或抽象类可以替代，且需要访问其特定的方法实现
        $this->joinPoint = $this->createMock(JoinPoint::class);
        $this->joinPoint->method('getInstance')->willReturn($testService);
        $this->joinPoint->method('getMethod')->willReturn('testMethod');
        $this->joinPoint->method('getParams')->willReturn(['param' => 'test_value']);
        $this->joinPoint->method('getReturnValue')->willReturn('Result for: test_value');
        $this->joinPoint->method('getUniqueId')->willReturn('CacheTestService.testMethod.test_value');

        // 创建不带注解方法的 JoinPoint
        // 使用具体类 JoinPoint 进行 mock 是必要的，因为：
        // 1. JoinPoint 类包含了 AOP 框架的核心状态和行为
        // 2. 该类的实例在运行时由 AOP 框架动态创建，测试中需要模拟其完整行为
        // 3. 没有合适的接口或抽象类可以替代，且需要访问其特定的方法实现
        $this->regularJoinPoint = $this->createMock(JoinPoint::class);
        $this->regularJoinPoint->method('getInstance')->willReturn($testService);
        $this->regularJoinPoint->method('getMethod')->willReturn('regularMethod');
    }

    public function testGetAttribute(): void
    {
        // 测试带 Cacheble 注解的方法
        $attribute = $this->traitInstance->exposeGetAttribute($this->joinPoint);
        $this->assertInstanceOf(CacheAttributeInterface::class, $attribute);

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
        if (is_resource($resource)) {
            fclose($resource);
        }

        $this->assertFalse($this->traitInstance->exposeCouldResultSave(function (): void {
        }));
    }
}
