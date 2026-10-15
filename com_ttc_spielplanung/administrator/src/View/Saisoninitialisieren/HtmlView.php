<?php
namespace Ttc\Component\Spielplanung\Administrator\View\Saisoninitialisieren;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $season;
    protected $canCreate;

    public function display($tpl = null)
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('core.manage', 'com_ttc_spielplanung')) {
            throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_SEASON_DENIED'), 403);
        }
        $this->season = $this->getModel()->getSeason();
        $this->canCreate = $user->authorise('core.create', 'com_ttc_spielplanung');
        return parent::display($tpl);
    }
}
