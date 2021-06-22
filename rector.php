<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::DEAD_CODE);
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);
    $parameters->set(Option::ENABLE_CACHE, true);

    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class);
};
