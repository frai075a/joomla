<?php
namespace Ttc\Component\Spielplanung\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

class SaisoninitialisierenController extends BaseController
{
    public function initialize()
    {
        $this->checkToken('post');
        $app = Factory::getApplication();
        try {
            $created = $this->getModel('Saisoninitialisieren', 'Administrator')->initialize();
            $app->enqueueMessage(Text::_($created
                ? 'COM_TTC_SPIELPLANUNG_SEASON_SUCCESS'
                : 'COM_TTC_SPIELPLANUNG_SEASON_ALREADY_EXISTS'));
        } catch (\Exception $e) {
            $app->enqueueMessage(Text::_('COM_TTC_SPIELPLANUNG_SEASON_FAILED'), 'error');
        }
        $this->setRedirect('index.php?option=com_ttc_spielplanung&view=saisoninitialisieren');
    }
}
