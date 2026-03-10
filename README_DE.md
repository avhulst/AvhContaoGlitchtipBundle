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

## Standardwerte überschreiben

Alle Einstellungen lassen sich in `config/packages/sentry.yaml` überschreiben. Projekt-Konfiguration hat Vorrang vor den Bundle-Standardwerten.
