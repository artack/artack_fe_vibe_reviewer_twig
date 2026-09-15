<?php

declare(strict_types=1);

namespace Artack\FeReviewerTwig\Twig;

use Twig\Environment;
use Twig\Node\EmbedNode;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\IncludeNode;
use Twig\Node\Node;
use Twig\Node\PrintNode;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * Umschließt eingebundene Teil-Templates beim Kompilieren mit Herkunfts-Markern
 * (<!--fe:in <partial>--> … <!--fe:out-->). Erfasst beide Formen:
 *
 *   - Tag-Form:        {% include 'x.html.twig' %} / {% embed %}
 *   - Funktions-Form:  {{ include('x.html.twig') }}
 *
 * Bewusst NUR diese Einbindungen — sie sind fast immer Flow-Content (Partials) und
 * liegen praktisch nie in <head>/<title>/<script>. Ganze Module werden absichtlich
 * NICHT umschlossen (kein Kommentar vor <!doctype>). Die Funktions-Form kann selten
 * in einem Attribut/JS stehen ({{ }} ist überall erlaubt); dort wäre der Kommentar
 * unpassend — in der Praxis rendert include() aber Content, nicht Attributwerte.
 */
final class SourceMarkerNodeVisitor implements NodeVisitorInterface
{
    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        // Tag-Form: {% include %} / {% embed %}
        if ($node instanceof IncludeNode) {
            return $this->wrap($node, $this->payloadForInclude($node));
        }

        // Funktions-Form: {{ include('…') }}  (PrintNode um eine include-FunctionExpression)
        if ($node instanceof PrintNode && $node->hasNode('expr')) {
            $expr = $node->getNode('expr');
            if ($expr instanceof FunctionExpression && 'include' === $expr->getAttribute('name')) {
                return $this->wrap($node, $this->payloadForFunction($expr));
            }
        }

        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }

    private function wrap(Node $node, ?string $payload): Node
    {
        if (null === $payload) {
            return $node;
        }

        return new Node([
            new MarkerNode('in', $payload),
            $node,
            new MarkerNode('out'),
        ], [], $node->getTemplateLine());
    }

    /**
     * {% include 'x.html.twig' %} → Partial-Pfad; sonst Ort der Anweisung (Template:Zeile).
     */
    private function payloadForInclude(IncludeNode $node): ?string
    {
        // Embeds kompilieren zu generierten Namen → kein sinnvoller Pfad; Ort verwenden.
        if (!$node instanceof EmbedNode && $node->hasNode('expr')) {
            $path = $this->constantPath($node->getNode('expr'));
            if (null !== $path) {
                return $path;
            }
        }

        return $this->location($node);
    }

    /**
     * {{ include('x.html.twig') }} → Partial-Pfad aus dem ersten Argument; sonst Ort.
     */
    private function payloadForFunction(FunctionExpression $fn): ?string
    {
        if ($fn->hasNode('arguments')) {
            foreach ($fn->getNode('arguments') as $arg) {
                $path = $arg instanceof Node ? $this->constantPath($arg) : null;

                return $path ?? $this->location($fn); // nur das erste Argument zählt
            }
        }

        return $this->location($fn);
    }

    /** Literalen String (oder ersten Kandidaten einer Array-Angabe) aus einem Konstanten-Ausdruck. */
    private function constantPath(Node $expr): ?string
    {
        if (!$expr instanceof ConstantExpression) {
            return null;
        }
        $v = $expr->getAttribute('value');
        if (\is_string($v)) {
            return $v;
        }
        if (\is_array($v) && isset($v[0]) && \is_string($v[0])) {
            return $v[0];
        }

        return null;
    }

    private function location(Node $node): ?string
    {
        $tpl = $node->getTemplateName();
        $line = $node->getTemplateLine();

        return null !== $tpl ? $tpl.':'.$line : null;
    }
}
