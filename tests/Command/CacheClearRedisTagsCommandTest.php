<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\Symfony\AopCacheBundle\Command\CacheClearRedisTagsCommand;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

/**
 * @internal
 */
#[CoversClass(CacheClearRedisTagsCommand::class)]
#[RunTestsInSeparateProcesses]
final class CacheClearRedisTagsCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 无需额外设置
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CacheClearRedisTagsCommand::class);
        $this->assertInstanceOf(CacheClearRedisTagsCommand::class, $command);

        return new CommandTester($command);
    }

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
        $this->assertTrue($reflection->isSubclassOf(Command::class));

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

    public function testCommandExecution(): void
    {
        $commandTester = $this->getCommandTester();
        $exitCode = $commandTester->execute([]);

        // 由于测试环境没有配置 RedisTagAwareAdapter，命令会返回失败
        $this->assertEquals(Command::FAILURE, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('找不到有效的Redis缓存', $output);
    }
}
