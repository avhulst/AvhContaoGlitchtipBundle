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
| **Ignored exceptions** | Contao control flow, 404 and bot noise (see below) |

### Ignored exceptions

These exceptions are not reported (`options.ignore_exceptions`). Sentry matches with `is_a()`, so subclasses are ignored too.

| Exception | Reason |
|---|---|
| `Contao\CoreBundle\Exception\ResponseException` | Control flow: Contao throws redirects, Ajax redirects and empty responses (`RedirectResponseException`, `AjaxRedirectResponseException`, `NoContentResponseException`) as exceptions. |
| `Contao\CoreBundle\Exception\NotFoundException` | 404 noise: `PageNotFoundException` for unknown URLs, mostly from bots and scanners. Contao also throws the base class itself in the back end when a record or file no longer exists (e.g. `Cannot load record "tl_page.id=20"` in `DC_Table`, `DC_Folder` and the CSV import), usually after it was deleted or through an outdated link. |
| `Contao\UnusedArgumentsException` | 404 noise: URLs with extra path fragments that no module consumed. |
| `Contao\CoreBundle\Exception\InvalidRequestTokenException` | Expired or missing CSRF tokens from old forms and bots, answered with 400. |
| `Symfony\Component\HttpKernel\Exception\NotFoundHttpException` | 404 noise from routes that do not exist. |
| `Symfony\Component\Security\Core\Exception\AccessDeniedException` | Bots and anonymous visitors on `/contao` and other protected areas. |

Real errors such as database deadlocks, mailer failures and other 500 errors are still reported. Sentry captures the exception before Contao converts it into an HTTP exception, so misconfigurations like `NoRootPageFoundException` or `NoLayoutSpecifiedException` stay visible as well.

Sentry checks every exception in the chain (including `getPrevious()`). An error that is wrapped in one of the classes above is dropped as well.

## Overriding defaults

All settings can be overridden in `config/packages/sentry.yaml`. Project-level configuration takes precedence over the bundle defaults.

`options.ignore_exceptions` is an exception to this: Symfony **appends** list entries, so a project list extends the bundle defaults instead of replacing them. Setting an empty list changes nothing.

```yaml
# config/packages/sentry.yaml
sentry:
    options:
        ignore_exceptions:
            - App\Exception\MyNoisyException   # added to the defaults
```

A default cannot be removed through `sentry.yaml`. If you need to report one of these exceptions again, remove it with a compiler pass in your own bundle:

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
