# ContaoGlitchtipBundle

**Glitchtip (Sentry-compatible) error tracking for Contao CMS.**

Wraps `sentry/sentry-symfony` as a Contao bundle with sensible defaults for Glitchtip.

## Setup

### Prerequisites

- Contao **5.3+** (Managed Edition)
- A [Glitchtip](https://glitchtip.com/) or Sentry-compatible instance

### Installation

```bash
composer require avh/contao-glitchtip-bundle
```

### Environment variable

Set `GLITCHTIP_DSN` in `.env.local`:

```dotenv
GLITCHTIP_DSN=https://your-key@glitchtip.example.com/1
```

> *If `GLITCHTIP_DSN` is not set, Sentry deactivates itself automatically. No `.env` entry is required for the bundle to install and run without errors.*

## Default configuration

The bundle prepends the following Sentry configuration automatically:

| Setting | Value |
|---|---|
| **DSN** | `GLITCHTIP_DSN` env var |
| **Error listener & handler** | enabled |
| **PII** | disabled (`send_default_pii: false`) |
| **Performance tracing** | 20% sample rate |
| **Tracing** | DBAL, Twig, Cache, HTTP Client |
| **Messenger** | enabled, soft fail capture |

## Overriding defaults

All settings can be overridden in `config/packages/sentry.yaml`. Project-level configuration takes precedence over the bundle defaults.
