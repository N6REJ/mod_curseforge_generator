<?php
defined('_JEXEC') or die;

use Joomla\CMS\Extension\ServiceProviderInterface;
use Joomla\CMS\Extension\Service\Provider\Module as ModuleProvider;
use Joomla\CMS\Extension\Service\Provider\WebAssetRegistry as WebAssetRegistryProvider;
use Joomla\DI\Container;

return new class () implements ServiceProviderInterface {
    public function register(Container $container)
    {
        // FIX: Point the Registry Provider to the deployed system media folder layout
        $container->register(new WebAssetRegistryProvider('media/mod_curseforge_generator'));

        // Register the module instantiation engine
        $container->register(new ModuleProvider('\\Joomla\\Module\\CurseforgeGenerator'));
    }
};
