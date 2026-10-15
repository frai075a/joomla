<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class TttrainingModel extends AdminModel
{
    protected $option = 'com_ttc_hallenbuchung';
    protected $text_prefix = 'COM_TTC_HALLENBUCHUNG';

    public function getTable($type = 'Tttraining', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_ttc_hallenbuchung.tttraining', 'tttraining', ['control' => 'jform', 'load_data' => $loadData]);
        if ($form && !Factory::getApplication()->getIdentity()->authorise('core.edit.state', $this->option)) {
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }
        return $form;
    }

    protected function loadFormData()
    {
        return Factory::getApplication()->getUserState('com_ttc_hallenbuchung.edit.tttraining.data', []) ?: $this->getItem();
    }
}
