<?php
defined('_JEXEC') or die;

use Joomla\CMS\Extension\ModuleInterface;
use Joomla\CMS\Factory;
use Joomla\Event\DispatcherInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\CMS\Extension\Service\Provider\Module as ModuleProvider;
use Joomla\CMS\Extension\Service\Provider\WebAssetRegistry as WebAssetRegistryProvider;

return new class () implements ServiceProviderInterface {
    public function register(Container $container)
    {
        // 1. Register the Web Asset Registry provider for your module
        $container->register(new WebAssetRegistryProvider('modules/mod_curseforge_generator'));

        // 2. Register the module itself
        $container->register(new ModuleProvider('\\Joomla\\Module\\CurseforgeGenerator'));
    }
};
