<?php
namespace Ttc\Component\Spielplanung\Administrator\View\Mmb;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $categories;

    public function display($tpl = null)
    {
        $this->items = $this->getModel()->getItems();
        $this->state = $this->getModel()->getState();
        $this->pagination = $this->getModel()->getPagination();

        $model = $this->getModel();
        $this->categories = $model->getRelevantCategories();

        return parent::display($tpl);
    }
}
