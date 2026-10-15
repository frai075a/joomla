<?php
/**
 * @version    CVS: 2.1
 * @package    Com_Ttc_hallenbuchung
 * @author     Thorsten Austen <fb@ttc-nordend.de>
 * @copyright  Copyright (C) 2013-2019. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\Extension\Ttc_hallenbuchungComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;


/**
 * The Ttc_hallenbuchung service provider.
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

		$container->registerServiceProvider(new MVCFactory('\\Ttchallenbuchung\\Component\\Ttc_hallenbuchung'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\Ttchallenbuchung\\Component\\Ttc_hallenbuchung'));
		$container->registerServiceProvider(new RouterFactory('\\Ttchallenbuchung\\Component\\Ttc_hallenbuchung'));

		$container->set(
			ComponentInterface::class,
			function (Container $container)
			{
				$component = new Ttc_hallenbuchungComponent($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setRegistry($container->get(Registry::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));

				$component->setRouterFactory($container->get(RouterFactoryInterface::class));

				return $component;
			}
		);
	}
};
