<?php

declare(strict_types=1);

namespace Avh\Contao\GlitchtipBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ContaoGlitchtipBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('sentry', [
            'dsn' => '%env(default::GLITCHTIP_DSN)%',
            'register_error_listener' => true,
            'register_error_handler' => true,
            'options' => [
                'environment' => '%kernel.environment%',
                'send_default_pii' => false,
                'traces_sample_rate' => 0.2,
                // Sentry matches with is_a(), so subclasses are covered too. The class
                // names are strings to avoid a hard dependency on these classes.
                'ignore_exceptions' => [
                    // Contao control flow (redirects, Ajax redirects, empty responses)
                    'Contao\CoreBundle\Exception\ResponseException',
                    // 404 and bot noise
                    'Contao\CoreBundle\Exception\NotFoundException',
                    'Contao\UnusedArgumentsException',
                    'Contao\CoreBundle\Exception\InvalidRequestTokenException',
                    'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
                    'Symfony\Component\Security\Core\Exception\AccessDeniedException',
                ],
            ],
            'messenger' => [
                'enabled' => true,
                'capture_soft_fails' => true,
            ],
            'tracing' => [
                'enabled' => true,
                'dbal' => ['enabled' => true],
                'twig' => ['enabled' => true],
                'cache' => ['enabled' => true],
                'http_client' => ['enabled' => true],
            ],
        ]);
    }
}
