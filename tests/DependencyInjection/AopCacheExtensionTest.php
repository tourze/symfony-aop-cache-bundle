<?php

declare(strict_types=1);

namespace Tourze\Symfony\AopCacheBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\Symfony\AopCacheBundle\DependencyInjection\AopCacheExtension;

/**
 * @internal
 */
#[CoversClass(AopCacheExtension::class)]
final class AopCacheExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
