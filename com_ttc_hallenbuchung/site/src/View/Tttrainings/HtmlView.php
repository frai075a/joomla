<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\View\Tttrainings;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView
{
    public $items;
    public $pagination;
    public $state;
    public function display($tpl = null)
    {
        $model = $this->getModel();
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        if ($errors = $model->getErrors()) { throw new \RuntimeException(implode("\n", $errors)); }
        $this->getDocument()->setTitle(Text::_('COM_TTC_HALLENBUCHUNG_TT_TITLE'));
        parent::display($tpl);
    }
}
