<?php

declare(strict_types=1);

namespace Artack\FeReviewerTwig\Twig;

use Twig\Environment;
use Twig\Node\EmbedNode;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\IncludeNode;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * Umschließt {% include %}/{% embed %} beim Kompilieren mit Herkunfts-Markern
 * (<!--fe:in <partial>--> … <!--fe:out-->).
 *
 * Bewusst NUR Includes/Embeds — das sind fast immer Flow-Content (Partials) und
 * liegen praktisch nie in <head>/<title>/<script> oder in Attributen. Ganze Module
 * werden absichtlich NICHT umschlossen (kein Kommentar vor <!doctype>).
 */
final class SourceMarkerNodeVisitor implements NodeVisitorInterface
{
    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        if ($node instanceof IncludeNode) {
            $payload = $this->payloadFor($node);
            if (null === $payload) {
                return $node;
            }

            return new Node([
                new MarkerNode('in', $payload),
                $node,
                new MarkerNode('out'),
            ], [], $node->getTemplateLine());
        }

        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }

    /**
     * Marker-Payload: den eingebundenen Partial-Pfad bei literalem
     * {% include 'x.html.twig' %}, sonst den Ort der Anweisung (Template:Zeile).
     */
    private function payloadFor(IncludeNode $node): ?string
    {
        // Embeds kompilieren zu generierten Namen → kein sinnvoller Pfad; Ort verwenden.
        if (!$node instanceof EmbedNode && $node->hasNode('expr')) {
            $expr = $node->getNode('expr');
            if ($expr instanceof ConstantExpression) {
                $v = $expr->getAttribute('value');
                if (\is_string($v)) {
                    return $v;
                }
                // {% include ['a.html.twig','b.html.twig'] %} → erster Kandidat.
                if (\is_array($v) && isset($v[0]) && \is_string($v[0])) {
                    return $v[0];
                }
            }
        }

        $tpl = $node->getTemplateName();
        $line = $node->getTemplateLine();

        return null !== $tpl ? $tpl.':'.$line : null;
    }
}
