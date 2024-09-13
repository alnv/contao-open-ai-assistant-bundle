<?php

namespace Alnv\ContaoOpenAiAssistantBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Alnv\ContaoOpenAiAssistantBundle\AlnvContaoOpenAiAssistantBundle;

class Plugin implements BundlePluginInterface
{

    public function getBundles(ParserInterface $parser): array
    {

        return [
            BundleConfig::create(AlnvContaoOpenAiAssistantBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['contao-open-ai-assistant-bundle'])
        ];
    }
}