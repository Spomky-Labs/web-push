<?php

declare(strict_types=1);

namespace WebPush\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use WebPush\Bundle\WebPushBundle;

final class AppKernel extends Kernel
{
    public function __construct(string $environment)
    {
        parent::__construct($environment, true);
    }

    /**
     * @return iterable|BundleInterface[] An iterable of bundle instances
     */
    public function registerBundles(): iterable
    {
        return [new FrameworkBundle(), new DoctrineBundle(), new MonologBundle(), new WebPushBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}
