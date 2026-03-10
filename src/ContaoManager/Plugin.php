<?php

declare(strict_types=1);

namespace Avh\Contao\GlitchtipBundle\ContaoManager;

use Avh\Contao\GlitchtipBundle\ContaoGlitchtipBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Sentry\SentryBundle\SentryBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoGlitchtipBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
            BundleConfig::create(SentryBundle::class)
                ->setLoadAfter([ContaoGlitchtipBundle::class]),
        ];
    }
}
