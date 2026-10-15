<?php
namespace Ttc\Component\Hallenschliesszeiten\Administrator\Extension;
defined('_JEXEC') or die;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\Database\DatabaseInterface;
use Psr\Container\ContainerInterface;
use Ttc\Component\Hallenschliesszeiten\Administrator\Service\Html\HALLENSCHLIESSZEITEN;
use Ttc\Component\Hallenschliesszeiten\Administrator\Service\Html\HALLENINFOS;
class HallenschliesszeitenComponent extends MVCComponent implements BootableExtensionInterface, RouterServiceInterface
{
    use RouterServiceTrait;
    use HTMLRegistryAwareTrait;
    public function boot(ContainerInterface $container)
    {
        $db = $container->get(DatabaseInterface::class);
        $this->getRegistry()->register('hallenschliesszeiten', new HALLENSCHLIESSZEITEN($db));
        $this->getRegistry()->register('halleninfos', new HALLENINFOS($db));
    }
}
