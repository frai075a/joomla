<?php
/**
 * @version    CVS: 1.3.0
 * @package    Com_Hallenschliesszeiten
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
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
use Ttc\Component\Hallenschliesszeiten\Administrator\Extension\HallenschliesszeitenComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;


/**
 * The Hallenschliesszeiten service provider.
 *
 * @since  1.3.0
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
	 * @since   1.3.0
	 */
	public function register(Container $container)
	{

		$container->registerServiceProvider(new CategoryFactory('\\Ttc\\Component\\Hallenschliesszeiten'));
		$container->registerServiceProvider(new MVCFactory('\\Ttc\\Component\\Hallenschliesszeiten'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\Ttc\\Component\\Hallenschliesszeiten'));
		$container->registerServiceProvider(new RouterFactory('\\Ttc\\Component\\Hallenschliesszeiten'));

		$container->set(
			ComponentInterface::class,
			function (Container $container)
			{
				$component = new HallenschliesszeitenComponent($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setRegistry($container->get(Registry::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));

				return $component;
			}
		);
	}
};
