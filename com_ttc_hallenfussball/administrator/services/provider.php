<?php
/**
 * @version    CVS: 2.1
 * @package    Com_Ttc_hallenfussball
 * @author     Thorsten Austen <fb@ttc-nordend.de>
 * @copyright  Copyright (C) 2013-2019. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Ttchallenfussball\Component\Ttc_hallenfussball\Administrator\Extension\Ttc_hallenfussballComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;


/**
 * The Ttc_hallenfussball service provider.
 *
 * @since  2.1
 */
return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   2.1
	 */
	public function register(Container $container)
	{

		$container->registerServiceProvider(new CategoryFactory('\\Ttchallenfussball\\Component\\Ttc_hallenfussball'));
		$container->registerServiceProvider(new MVCFactory('\\Ttchallenfussball\\Component\\Ttc_hallenfussball'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\Ttchallenfussball\\Component\\Ttc_hallenfussball'));
		$container->registerServiceProvider(new RouterFactory('\\Ttchallenfussball\\Component\\Ttc_hallenfussball'));

		$container->set(
			ComponentInterface::class,
			function (Container $container)
			{
				$component = new Ttc_hallenfussballComponent($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setRegistry($container->get(Registry::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));

				return $component;
			}
		);
	}
};
