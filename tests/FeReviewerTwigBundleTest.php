<?php

declare(strict_types=1);

namespace Artack\FeReviewerTwig\Tests;

use Artack\FeReviewerTwig\FeReviewerTwigBundle;
use Artack\FeReviewerTwig\Twig\FeReviewerTwigExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class FeReviewerTwigBundleTest extends TestCase
{
    public function testExtensionIsRegisteredAsTwigExtensionInDev(): void
    {
        $container = $this->buildContainer('dev');

        self::assertTrue($container->hasDefinition(FeReviewerTwigExtension::class));
        self::assertTrue($container->getDefinition(FeReviewerTwigExtension::class)->hasTag('twig.extension'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function nonDevEnvironments(): iterable
    {
        yield 'prod' => ['prod'];
        yield 'test' => ['test'];
        yield 'staging' => ['staging'];
    }

    #[DataProvider('nonDevEnvironments')]
    public function testNothingIsRegisteredOutsideDev(string $environment): void
    {
        $container = $this->buildContainer($environment);

        self::assertFalse($container->hasDefinition(FeReviewerTwigExtension::class));
    }

    private function buildContainer(string $environment): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', $environment);
        $container->setParameter('kernel.build_dir', sys_get_temp_dir());

        $bundle = new FeReviewerTwigBundle();
        $extension = $bundle->getContainerExtension();
        self::assertNotNull($extension);
        $extension->load([], $container);

        return $container;
    }
}
