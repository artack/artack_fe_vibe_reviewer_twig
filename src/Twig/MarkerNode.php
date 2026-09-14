<?php

declare(strict_types=1);

namespace Artack\FeReviewerTwig\Twig;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Gibt einen unsichtbaren Herkunfts-Marker ins gerenderte HTML aus, z. B.
 * <!--fe:in templates/product/_card.html.twig--> ... <!--fe:out-->.
 *
 * Die fe-reviewer-Toolbar liest diese Kommentare und ordnet ein angeklicktes
 * Element dem erzeugenden Template zu. Nur im Dev-Modus aktiv.
 */
final class MarkerNode extends Node
{
    public function __construct(private readonly string $kind, private readonly string $payload = '')
    {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        // '-->' darf im Payload nicht vorkommen (würde den Kommentar vorzeitig schließen).
        $p = str_replace(['--', "\r", "\n"], ['- -', ' ', ' '], $this->payload);
        $comment = '<!--fe:'.$this->kind.('' !== $p ? ' '.$p : '').'-->';
        $compiler->write('echo ')->string($comment)->raw(";\n");
    }
}
