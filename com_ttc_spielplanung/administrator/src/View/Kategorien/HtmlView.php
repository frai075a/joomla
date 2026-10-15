<?php
namespace Ttc\Component\Spielplanung\Administrator\View\Kategorien;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null)
    {
        $this->items = $this->getModel()->getItems();
        $this->state = $this->getModel()->getState();
        $this->pagination = $this->getModel()->getPagination();

        return parent::display($tpl);
    }
}
