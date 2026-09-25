# artack/fe-reviewer-twig

Dev-only Twig-Instrumentierung für die [fe-reviewer](https://github.com/artack/artack_fe_vibe_reviewer)-Toolbar.
Ordnet ein im Browser angeklicktes DOM-Element automatisch dem **Twig-Template** zu, das es erzeugt hat —
**ohne deine Templates zu verändern**.

Beim Rendern (nur Dev) schreibt Twig unsichtbare Herkunfts-Marker ins HTML:

```html
<!--fe:in product/_card.html.twig-->
  <div class="card">…</div>
<!--fe:out-->
```

Die Toolbar liest diese Kommentare und schickt dem Reviewer statt „irgendwo" ein konkretes
`product/_card.html.twig` mit — deutlich weniger Raten.

## Installation

Solange nicht auf Packagist: VCS-Repository in der `composer.json` des Zielprojekts eintragen.
Das Repo ist **public**, daher genügt die HTTPS-URL — **kein Token, kein SSH** nötig (Composer
lädt das Dist-Zip über die GitHub-API, funktioniert auch in Containern ohne git):

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/artack/artack_fe_vibe_reviewer_twig.git" }
]
```

… dann als Dev-Abhängigkeit installieren:

```bash
composer require --dev artack/fe-reviewer-twig
```

Bundle **nur im Dev-Modus** aktivieren — in `config/bundles.php`:

```php
return [
    // …
    Artack\FeReviewerTwig\FeReviewerTwigBundle::class => ['dev' => true],
];
```

Cache leeren:

```bash
bin/console cache:clear
```

Prüfen: eine Seite mit `{% include %}` laden und im Quelltext nach `<!--fe:in` suchen.

Das Bundle registriert seine Twig-Extension zusätzlich **selbst nur im Dev-Environment** — doppelt
abgesichert, falls der Bundle-Eintrag versehentlich für alle Umgebungen gilt.

## Was instrumentiert wird

Beide Einbindungs-Formen — die sichersten Injektionspunkte (fast immer Flow-Content,
praktisch nie in `<head>/<title>/<script>`). Ganze Templates (`ModuleNode`) werden
absichtlich nicht umschlossen (kein Kommentar vor `<!doctype>`).

- Tag-Form `{% include '_card.html.twig' %}` → Payload = der Partial-Pfad.
- **Funktions-Form `{{ include('_card.html.twig') }}`** → Payload = der Partial-Pfad (seit v0.2.0).
- Dynamisches Include / `{% embed %}` → Payload = Ort der Anweisung (`template:zeile`).
- Selten steht `{{ include(...) }}` in einem Attribut/JS statt im Content — dort wäre der
  Kommentar unpassend. In der Praxis rendert `include()` Content, daher ist das Risiko gering.

Elemente ausserhalb jedes Includes bekommen keinen Marker → die Toolbar fällt dort auf Vorfahrenkette
+ Screenshot zurück. Je mehr Partials/Components, desto höher die Trefferquote.

## Prod-Sicherheit

Im Prod-/Test-Environment registriert das Bundle nichts. Nach dem Ein-/Ausschalten `cache:clear`.
Vendor-Templates bekommen ebenfalls Marker; die überschreibt man (`templates/bundles/…`).

## Kompatibilität

Getestet mit Symfony 8.1 und Twig 3.28 (Constraints: Symfony 7/8). Benötigt PHP ≥ 8.2.
