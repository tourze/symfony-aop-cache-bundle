<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\Symfony\AopCacheBundle\AopCacheBundle;

/**
 * @internal
 */
#[CoversClass(AopCacheBundle::class)]
#[RunTestsInSeparateProcesses]
final class AopCacheBundleTest extends AbstractBundleTestCase
{
}
