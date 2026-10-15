<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\View\Tttrainingform;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView
{
    public $item;
    public $form;
    public function display($tpl = null)
    {
        $model = $this->getModel();
        $this->item = $model->getItem();
        $this->form = $model->getForm();
        if (!$this->form) { throw new \RuntimeException($model->getError()); }
        $this->getDocument()->setTitle(Text::_('COM_TTC_HALLENBUCHUNG_TT_BOOK'));
        
        parent::display($tpl);
    }
}
