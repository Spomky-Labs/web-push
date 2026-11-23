<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

$builder = RectorConfig::configure();
if (file_exists('/tools/.composer/vendor-bin/phpunit/vendor/autoload.php')) {
    $builder->withAutoloadPaths(['/tools/.composer/vendor-bin/phpunit/vendor/autoload.php']);
}
$builder->withSets([
    SetList::DEAD_CODE,
    LevelSetList::UP_TO_PHP_82,
    DoctrineSetList::DOCTRINE_CODE_QUALITY,
    DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
    PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
]);
// Symfony and PHPUnit are deliberately left out: composer resolves the highest
// versions available to the CI container (Symfony 8, PHPUnit 12) while the library
// still supports Symfony ^6.2 and the test suite runs on PHPUnit 11. Version-pinned
// migration sets would rewrite the code above that floor.
$builder->withComposerBased(twig: true, doctrine: true);
$builder->withPhpVersion(PhpVersion::PHP_82);
$builder->withPaths(
    [
        __DIR__ . '/../src',
        __DIR__ . '/../tests',
        __DIR__ . '/../castor.php',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ]
);
$builder->withSkip([PreferPHPUnitThisCallRector::class]);
$builder->withParallel();
$builder->withImportNames();

return $builder;
