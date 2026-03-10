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
