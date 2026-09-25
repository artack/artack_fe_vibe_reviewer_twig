# AGENTS.md

## Was das ist

Dev-only Symfony-Bundle (Library, kein App-Kernel) für die [fe-reviewer](https://github.com/artack/artack_fe_vibe_reviewer)-Toolbar. Beim Twig-Kompilieren werden Includes mit HTML-Kommentaren `<!--fe:in <partial>-->` … `<!--fe:out-->` umschlossen, damit die Toolbar ein angeklicktes DOM-Element seinem Quell-Template zuordnen kann. Details und Installation im Zielprojekt: `README.md`.

Das Marker-Format ist der **Vertrag mit der Toolbar**. Änderungen an Prefix, Payload-Form oder Escaping brechen den Consumer und brauchen eine abgestimmte Änderung dort.

## Befehle

```bash
composer install            # Projekt-Abhängigkeiten (für PHPStan nötig)
composer update:tools       # QA-Tools unter tools/<tool>/ installieren/aktualisieren
composer test               # phpcs + phpstan + phpunit
composer phpcs / phpcs:fix  # php-cs-fixer (Haus-Regelsatz inkl. risky, declare_strict_types)
composer phpstan            # Level 9 auf src/
composer phpunit            # alle Tests
vendor/bin/phpunit --filter testEmbedFallsBackToLocation   # einzelner Test
```

Die Tests rendern Templates über eine Twig-`Environment` mit `ArrayLoader` und der Extension und prüfen das erzeugte HTML wortwörtlich. Neue Marker-Fälle gehören als Render-Test in `tests/Twig/SourceMarkerNodeVisitorTest.php`. Der Bundle-Test ruft `getContainerExtension()->load()` direkt auf, weil `ContainerBuilder::compile()` die ungenutzte private Service-Definition entfernen würde. End-to-End-Verifikation nur über ein Host-Symfony-Projekt: `cache:clear`, Seite mit `{% include %}` laden, im Quelltext nach `<!--fe:in` suchen.

Jedes Tool hat unter `tools/<tool>/` eine eigene `composer.json` + `composer.lock` (committed), damit Tool-Abhängigkeiten nicht mit dem Bundle kollidieren. Die Projekt-`composer.lock` ist ignoriert (Library).

## Architektur

Vier Klassen, eine Kette, alles zur Compile-Zeit von Twig:

1. `FeReviewerTwigBundle` registriert `FeReviewerTwigExtension` nur bei `kernel.environment === dev`. Das ist die zweite Absicherung neben `['dev' => true]` in `bundles.php`; beide bleiben.
2. `FeReviewerTwigExtension` liefert den NodeVisitor über `getNodeVisitors()`. Symfony verdrahtet NodeVisitors nicht über den Container-Tag `twig.node_visitor`, nur über eine Extension.
3. `SourceMarkerNodeVisitor::leaveNode()` entscheidet, was umschlossen wird und mit welchem Payload.
4. `MarkerNode::compile()` gibt den Kommentar als `echo` aus und entschärft `--` und Zeilenumbrüche im Payload, damit der Kommentar nicht vorzeitig schliesst.

Instrumentiert werden **ausschliesslich Includes**, nie ganze Templates (`ModuleNode`), sonst landet ein Kommentar vor `<!doctype>`:

| Form | Payload |
|---|---|
| `{% include 'x.html.twig' %}` literal | Partial-Pfad |
| `{{ include('x.html.twig') }}` literal (PrintNode um FunctionExpression `include`) | Partial-Pfad, nur erstes Argument zählt |
| dynamischer Pfad oder `{% embed %}` | `template:zeile` der Anweisung (Embeds kompilieren zu generierten Namen) |
| literales Ziel mit `.css/.js/.svg/.json/.xml/.txt` (auch `.twig`-Suffix) | kein Marker |

Der Bundle-Code ist Symfony 6.4/7/8-kompatibel, deshalb keine APIs nutzen, die nur in einer Major-Version existieren.

## Konventionen

Commits, README und Docblocks sind auf Deutsch.
