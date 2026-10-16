# ContaoGlitchtipBundle

**Glitchtip-basiertes (Sentry-kompatibles) Error-Tracking für Contao CMS.**

Kapselt `sentry/sentry-symfony` als Contao-Bundle mit sinnvollen Standardwerten für Glitchtip.

## Setup

### Voraussetzungen

- Contao **5.3+** (Managed Edition)
- Eine [Glitchtip](https://glitchtip.com/)- oder Sentry-kompatible Instanz

### Installation

```bash
composer require avh/contao-glitchtip-bundle
```

### Umgebungsvariable

`GLITCHTIP_DSN` in `.env.local` setzen:

```dotenv
GLITCHTIP_DSN=https://your-key@glitchtip.example.com/1
```

> *Wenn `GLITCHTIP_DSN` nicht gesetzt ist, deaktiviert sich Sentry automatisch. Kein `.env`-Eintrag nötig, damit das Bundle fehlerfrei installiert und ausgeführt wird.*

## Standardkonfiguration

Das Bundle stellt folgende Sentry-Konfiguration automatisch bereit:

| Einstellung | Wert |
|---|---|
| **DSN** | `GLITCHTIP_DSN` Umgebungsvariable |
| **Error-Listener & -Handler** | aktiviert |
| **PII** | deaktiviert (`send_default_pii: false`) |
| **Performance-Tracing** | 20 % Sample-Rate |
| **Tracing** | DBAL, Twig, Cache, HTTP Client |
| **Messenger** | aktiviert, Soft-Fail-Erfassung |
| **Ignorierte Exceptions** | Contao-Kontrollfluss, 404- und Bot-Rauschen (siehe unten) |

### Ignorierte Exceptions

Diese Exceptions werden nicht gemeldet (`options.ignore_exceptions`). Sentry vergleicht per `is_a()`, Unterklassen werden also ebenfalls ignoriert.

| Exception | Begründung |
|---|---|
| `Contao\CoreBundle\Exception\ResponseException` | Kontrollfluss: Contao wirft Redirects, Ajax-Redirects und leere Antworten (`RedirectResponseException`, `AjaxRedirectResponseException`, `NoContentResponseException`) als Exception. |
| `Contao\CoreBundle\Exception\NotFoundException` | 404-Rauschen: `PageNotFoundException` bei unbekannten URLs, meist von Bots und Scannern. Contao wirft die Basisklasse auch selbst im Backend, wenn ein Datensatz oder eine Datei nicht mehr existiert (z. B. `Cannot load record "tl_page.id=20"` in `DC_Table`, `DC_Folder` und beim CSV-Import), meist nach dem Löschen oder über einen veralteten Link. |
| `Contao\UnusedArgumentsException` | 404-Rauschen: URLs mit zusätzlichen Pfadteilen, die kein Modul verarbeitet hat. |
| `Contao\CoreBundle\Exception\InvalidRequestTokenException` | Abgelaufene oder fehlende CSRF-Tokens aus alten Formularen und von Bots, beantwortet mit 400. |
| `Symfony\Component\HttpKernel\Exception\NotFoundHttpException` | 404-Rauschen von Routen, die es nicht gibt. |
| `Symfony\Component\Security\Core\Exception\AccessDeniedException` | Bots und anonyme Besucher auf `/contao` und anderen geschützten Bereichen. |

Echte Fehler wie DB-Deadlocks, Mailer-Fehler und andere 500er werden weiterhin gemeldet. Sentry erfasst die Exception, bevor Contao sie in eine HTTP-Exception umwandelt. Fehlkonfigurationen wie `NoRootPageFoundException` oder `NoLayoutSpecifiedException` bleiben deshalb ebenfalls sichtbar.

Sentry prüft jede Exception der Kette (inklusive `getPrevious()`). Ein Fehler, der in eine der obigen Klassen verpackt ist, wird also ebenfalls verworfen.

## Standardwerte überschreiben

Alle Einstellungen lassen sich in `config/packages/sentry.yaml` überschreiben. Projekt-Konfiguration hat Vorrang vor den Bundle-Standardwerten.

Ausnahme ist `options.ignore_exceptions`: Symfony **hängt** Listeneinträge **an**. Eine Projekt-Liste ergänzt die Bundle-Standardwerte, statt sie zu ersetzen. Eine leere Liste ändert nichts.

```yaml
# config/packages/sentry.yaml
sentry:
    options:
        ignore_exceptions:
            - App\Exception\MyNoisyException   # ergänzt die Standardwerte
```

Ein Standardwert lässt sich über `sentry.yaml` nicht entfernen. Soll eine dieser Exceptions wieder gemeldet werden, entfernt ein Compiler-Pass im eigenen Bundle sie:

```php
public function process(ContainerBuilder $container): void
{
    $definition = $container->getDefinition('sentry.client.options');
    $options = $definition->getArgument(0);
    $options['ignore_exceptions'] = array_values(array_diff(
        $options['ignore_exceptions'],
        ['Symfony\Component\Security\Core\Exception\AccessDeniedException'],
    ));
    $definition->replaceArgument(0, $options);
}
```
