<?php
namespace Ttc\Component\Spielplanung\Administrator\View\Eintraege;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $canDelete;
    protected $players;
    protected $playerId;
    protected $sort;
    protected $direction;

    public function display($tpl = null)
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('core.manage', 'com_ttc_spielplanung')) {
            throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_DELETE_DENIED'), 403);
        }
        $model = $this->getModel();
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->players = $model->getPlayers();
        $this->playerId = (int) $model->getState('filter.player_id');
        $this->sort = $model->getState('filter.sort');
        $this->direction = $model->getState('filter.direction');
        $this->canDelete = $user->authorise('core.delete', 'com_ttc_spielplanung');
        return parent::display($tpl);
    }
}
