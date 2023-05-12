<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\Unit\Extension;

use WebPush\Bundle\DependencyInjection\WebPushExtension;

/**
 * @internal
 */
abstract class AbstractExtensionTestCase extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [new WebPushExtension('webpush')];
    }
}
