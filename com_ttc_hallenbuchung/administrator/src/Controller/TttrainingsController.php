<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class TttrainingsController extends AdminController
{
    public function getModel($name = 'Tttraining', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true] + $config);
    }
}
