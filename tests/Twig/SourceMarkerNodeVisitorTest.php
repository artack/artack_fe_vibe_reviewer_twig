<?php

declare(strict_types=1);

namespace Artack\FeReviewerTwig\Tests\Twig;

use Artack\FeReviewerTwig\Twig\FeReviewerTwigExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class SourceMarkerNodeVisitorTest extends TestCase
{
    private const CARD = '<div class="card">Card</div>';

    public function testTagIncludeWithLiteralPathIsWrappedWithPartialPath(): void
    {
        $html = $this->render('<main>{% include "product/_card.html.twig" %}</main>');

        self::assertSame(
            '<main><!--fe:in product/_card.html.twig-->'.self::CARD.'<!--fe:out--></main>',
            $html,
        );
    }

    public function testFunctionIncludeWithLiteralPathIsWrappedWithPartialPath(): void
    {
        $html = $this->render('<main>{{ include("product/_card.html.twig") }}</main>');

        self::assertSame(
            '<main><!--fe:in product/_card.html.twig-->'.self::CARD.'<!--fe:out--></main>',
            $html,
        );
    }

    public function testDynamicTagIncludeFallsBackToLocation(): void
    {
        $html = $this->render("<main>\n{% include partial %}</main>", ['partial' => 'product/_card.html.twig']);

        self::assertSame(
            "<main>\n<!--fe:in page.html.twig:2-->".self::CARD.'<!--fe:out--></main>',
            $html,
        );
    }

    public function testDynamicFunctionIncludeFallsBackToLocation(): void
    {
        $html = $this->render("<main>\n{{ include(partial) }}</main>", ['partial' => 'product/_card.html.twig']);

        self::assertSame(
            "<main>\n<!--fe:in page.html.twig:2-->".self::CARD.'<!--fe:out--></main>',
            $html,
        );
    }

    public function testEmbedFallsBackToLocation(): void
    {
        $html = $this->render(
            "<main>\n{% embed 'layout/_box.html.twig' %}{% block body %}Inhalt{% endblock %}{% endembed %}</main>",
        );

        self::assertSame(
            "<main>\n<!--fe:in page.html.twig:2--><section>Inhalt</section><!--fe:out--></main>",
            $html,
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function nonHtmlTargets(): iterable
    {
        yield 'css' => ['assets/inline.css'];
        yield 'css.twig' => ['assets/inline.css.twig'];
        yield 'js' => ['assets/inline.js'];
        yield 'svg.twig' => ['icons/logo.svg.twig'];
        yield 'json' => ['data/config.json'];
        yield 'xml' => ['feed/sitemap.xml'];
        yield 'txt.twig' => ['mail/plain.txt.twig'];
    }

    #[DataProvider('nonHtmlTargets')]
    public function testNonHtmlIncludesAreNotMarked(string $target): void
    {
        $loader = [$target => 'RAW'];

        self::assertSame('<style>RAW</style>', $this->render('<style>{% include "'.$target.'" %}</style>', [], $loader));
        self::assertSame('<style>RAW</style>', $this->render('<style>{{ include("'.$target.'") }}</style>', [], $loader));
    }

    public function testWholeTemplateIsNotWrapped(): void
    {
        $html = $this->render('<!doctype html><html><body>Seite</body></html>');

        self::assertStringStartsWith('<!doctype html>', $html);
        self::assertStringNotContainsString('<!--fe:', $html);
    }

    public function testMarkersAreOnlyEmittedAroundIncludes(): void
    {
        $html = $this->render('<p>vorher</p>{% include "product/_card.html.twig" %}<p>nachher</p>');

        self::assertSame(
            '<p>vorher</p><!--fe:in product/_card.html.twig-->'.self::CARD.'<!--fe:out--><p>nachher</p>',
            $html,
        );
    }

    public function testNestedIncludesProduceNestedMarkers(): void
    {
        $html = $this->render(
            '{% include "outer.html.twig" %}',
            [],
            ['outer.html.twig' => '<ul>{% include "product/_card.html.twig" %}</ul>'],
        );

        self::assertSame(
            '<!--fe:in outer.html.twig--><ul><!--fe:in product/_card.html.twig-->'.self::CARD.'<!--fe:out--></ul><!--fe:out-->',
            $html,
        );
    }

    public function testDoubleDashInPayloadCannotCloseTheComment(): void
    {
        $html = $this->render(
            '{% include "weird--name.html.twig" %}',
            [],
            ['weird--name.html.twig' => 'X'],
        );

        self::assertSame('<!--fe:in weird- -name.html.twig-->X<!--fe:out-->', $html);
    }

    public function testWithoutExtensionNothingIsMarked(): void
    {
        $twig = new Environment(new ArrayLoader($this->templates(['page.html.twig' => '{% include "product/_card.html.twig" %}'])), ['cache' => false]);

        self::assertSame(self::CARD, $twig->render('page.html.twig'));
    }

    /**
     * @param array<string, mixed>  $context
     * @param array<string, string> $extraTemplates
     */
    private function render(string $page, array $context = [], array $extraTemplates = []): string
    {
        $twig = new Environment(
            new ArrayLoader($this->templates(['page.html.twig' => $page] + $extraTemplates)),
            ['cache' => false, 'strict_variables' => true],
        );
        $twig->addExtension(new FeReviewerTwigExtension());

        return $twig->render('page.html.twig', $context);
    }

    /**
     * @param array<string, string> $templates
     *
     * @return array<string, string>
     */
    private function templates(array $templates): array
    {
        return $templates + [
            'product/_card.html.twig' => self::CARD,
            'layout/_box.html.twig' => '<section>{% block body %}{% endblock %}</section>',
        ];
    }
}
