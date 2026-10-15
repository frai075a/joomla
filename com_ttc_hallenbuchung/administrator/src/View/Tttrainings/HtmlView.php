<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\View\Tttrainings;

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

        \Joomla\CMS\Toolbar\ToolbarHelper::title(Text::_('COM_TTC_HALLENBUCHUNG_TT_TITLE'));
        $user = Factory::getApplication()->getIdentity();
        if ($user->authorise('core.create', 'com_ttc_hallenbuchung')) { \Joomla\CMS\Toolbar\ToolbarHelper::addNew('tttraining.add'); }
        if ($user->authorise('core.edit', 'com_ttc_hallenbuchung')) { \Joomla\CMS\Toolbar\ToolbarHelper::editList('tttraining.edit'); }
        if ($user->authorise('core.edit.state', 'com_ttc_hallenbuchung')) {
            \Joomla\CMS\Toolbar\ToolbarHelper::publish('tttrainings.publish');
            \Joomla\CMS\Toolbar\ToolbarHelper::unpublish('tttrainings.unpublish');
        }
        if ($user->authorise('core.delete', 'com_ttc_hallenbuchung')) { \Joomla\CMS\Toolbar\ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'tttrainings.delete'); }
        parent::display($tpl);
    }
}
