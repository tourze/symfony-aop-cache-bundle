<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Tourze\Symfony\AopCacheBundle\Command\CacheClearRedisTagsCommand;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

class CacheClearRedisTagsCommandTest extends TestCase
{
    public function testCommandConfiguration(): void
    {
        // 通过反射验证命令配置
        $reflection = new \ReflectionClass(CacheClearRedisTagsCommand::class);

        // 验证命令注解
        $attributes = $reflection->getAttributes(AsCommand::class);
        $this->assertNotEmpty($attributes, 'AsCommand 注解应该存在');

        $asCommand = $attributes[0]->newInstance();
        $this->assertInstanceOf(AsCommand::class, $asCommand);
        $this->assertEquals('cache:redis-clear-tags', $asCommand->name);

        // 验证定时任务注解
        $cronAttributes = $reflection->getAttributes(AsCronTask::class);
        $this->assertNotEmpty($cronAttributes, 'AsCronTask 注解应该存在');
    }

    public function testCommandStructure(): void
    {
        $reflection = new \ReflectionClass(CacheClearRedisTagsCommand::class);

        // 验证命令是否继承了 Symfony\Component\Console\Command\Command
        $this->assertTrue($reflection->isSubclassOf(\Symfony\Component\Console\Command\Command::class));

        // 验证是否有执行方法
        $this->assertTrue($reflection->hasMethod('execute'));

        $executeMethod = $reflection->getMethod('execute');
        // 验证执行方法是否是 protected
        $this->assertTrue($executeMethod->isProtected());

        // 验证构造函数是否接受缓存参数
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);

        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('cache', $parameters[0]->getName());
    }
}
