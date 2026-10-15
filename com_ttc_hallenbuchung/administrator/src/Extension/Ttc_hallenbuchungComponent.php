<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;

/**
 * Hallenfussball stores participation records without categories or tags.
 */
class Ttc_hallenbuchungComponent extends MVCComponent implements RouterServiceInterface
{
    use RouterServiceTrait;
    use HTMLRegistryAwareTrait;
}
