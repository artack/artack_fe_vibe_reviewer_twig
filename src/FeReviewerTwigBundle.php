<?php

declare(strict_types=1);

namespace Artack\FeReviewerTwig;

use Artack\FeReviewerTwig\Twig\FeReviewerTwigExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * fe-reviewer Twig-Instrumentierung als Symfony-Bundle.
 *
 * Registriert die Twig-Extension, die {% include %}/{% embed %} mit Herkunfts-Markern
 * umschliesst — AUSSCHLIESSLICH im Dev-Environment. In prod/test wird nichts registriert:
 * keine Marker, kein Overhead, kein Risiko. Zusätzlich sollte der Bundle-Eintrag in
 * config/bundles.php auf ['dev' => true] stehen (siehe README).
 */
final class FeReviewerTwigBundle extends AbstractBundle
{
    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if ('dev' !== $builder->getParameter('kernel.environment')) {
            return;
        }

        $container->services()
            ->set(FeReviewerTwigExtension::class)
            ->tag('twig.extension');
    }
}
