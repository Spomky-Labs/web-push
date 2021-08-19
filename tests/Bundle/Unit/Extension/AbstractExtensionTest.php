<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\Extension;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use WebPush\Bundle\DependencyInjection\WebPushExtension;

/**
 * @internal
 */
abstract class AbstractExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new WebPushExtension('webpush'),
        ];
    }
}
