<?php

declare(strict_types=1);

namespace Avh\Contao\GlitchtipBundle\Tests;

use Avh\Contao\GlitchtipBundle\ContaoGlitchtipBundle;
use PHPUnit\Framework\TestCase;
use Sentry\SentryBundle\DependencyInjection\SentryExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;

class ContaoGlitchtipBundleTest extends TestCase
{
    private const DEFAULT_IGNORED_EXCEPTIONS = [
        'Contao\CoreBundle\Exception\ResponseException',
        'Contao\CoreBundle\Exception\NotFoundException',
        'Contao\UnusedArgumentsException',
        'Contao\CoreBundle\Exception\InvalidRequestTokenException',
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
        'Symfony\Component\Security\Core\Exception\AccessDeniedException',
    ];

    public function testIgnoresContaoNoiseByDefault(): void
    {
        $this->assertSame(self::DEFAULT_IGNORED_EXCEPTIONS, $this->getClientOptions()['ignore_exceptions']);
    }

    public function testProjectConfigurationAppendsToTheDefaults(): void
    {
        $options = $this->getClientOptions([
            'options' => ['ignore_exceptions' => ['App\Exception\FooException']],
        ]);

        $this->assertSame(
            [...self::DEFAULT_IGNORED_EXCEPTIONS, 'App\Exception\FooException'],
            $options['ignore_exceptions'],
        );
    }

    public function testProjectConfigurationCannotRemoveADefault(): void
    {
        $options = $this->getClientOptions([
            'options' => ['ignore_exceptions' => []],
        ]);

        $this->assertSame(self::DEFAULT_IGNORED_EXCEPTIONS, $options['ignore_exceptions']);
    }

    /**
     * @param array<string, mixed>|null $projectConfig
     *
     * @return array<string, mixed>
     */
    private function getClientOptions(array|null $projectConfig = null): array
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.project_dir', sys_get_temp_dir());
        $container->setParameter('kernel.build_dir', sys_get_temp_dir());
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir());
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', []);

        $bundle = new ContaoGlitchtipBundle();

        $container->registerExtension(new SentryExtension());
        $container->registerExtension($bundle->getContainerExtension());

        // Like a Contao project without its own sentry.yaml: the extension still
        // has to be loaded so that the prepended bundle defaults get processed.
        $container->loadFromExtension('sentry', $projectConfig ?? []);

        (new MergeExtensionConfigurationPass(['sentry']))->process($container);

        return $container->getDefinition('sentry.client.options')->getArgument(0);
    }
}
