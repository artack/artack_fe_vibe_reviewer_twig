<?php

declare(strict_types=1);

namespace Artack\FeReviewerTwig\Twig;

use Twig\Extension\AbstractExtension;

/**
 * Hängt den Herkunfts-NodeVisitor in Twig ein.
 *
 * Symfony verdrahtet Node-Visitors NICHT über den Container-Tag `twig.node_visitor`,
 * sondern nur über eine Twig-Extension (getNodeVisitors()).
 */
final class FeReviewerTwigExtension extends AbstractExtension
{
    public function getNodeVisitors(): array
    {
        return [new SourceMarkerNodeVisitor()];
    }
}
