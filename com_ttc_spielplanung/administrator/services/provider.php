<?php
defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Ttc\Component\Spielplanung\Administrator\Extension\SpielplanungComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->registerServiceProvider(new MVCFactory('\\Ttc\\Component\\Spielplanung'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Ttc\\Component\\Spielplanung'));

        $container->set(
            ComponentInterface::class,
            function (Container $container)
            {
                $component = new SpielplanungComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(\Joomla\CMS\MVC\Factory\MVCFactoryInterface::class));

                return $component;
            }
        );
    }
};
