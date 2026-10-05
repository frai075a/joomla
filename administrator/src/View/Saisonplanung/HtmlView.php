<?php
namespace Ttc\Component\Spielplanung\Administrator\View\Saisonplanung;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $confirmedPlayers;
    protected $categories;

    public function display($tpl = null)
    {
        $this->items = $this->getModel()->getItems();
        $this->state = $this->getModel()->getState();
        $this->pagination = $this->getModel()->getPagination();

        $model = $this->getModel();
        $this->categories = $model->getRelevantCategories();

        $gameIds = array();
        foreach ($this->items as $item)
        {
            $gameIds[] = $item->game_id;
        }

        $this->confirmedPlayers = $model->getConfirmedPlayers($gameIds);

        return parent::display($tpl);
    }
}
