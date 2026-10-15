<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Helper\TttrainingHelper;

class TttrainingformModel extends FormModel
{
    protected $option = 'com_ttc_hallenbuchung';

    protected function populateState()
    {
        $this->setState('tttraining.id', Factory::getApplication()->getInput()->getInt('id'));
    }

    public function getTable($type = 'Tttraining', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    public function getItem($id = null)
    {
        $id = $id ?? (int) $this->getState('tttraining.id');
        $table = $this->getTable();
        $user = Factory::getApplication()->getIdentity();
        if ($id) {
            if (!$table->load($id)) { throw new \RuntimeException(Text::_('COM_TTC_HALLENBUCHUNG_TT_NOT_FOUND'), 404); }
            if (!TttrainingHelper::canEdit($table, $user)) { throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403); }
        } elseif (!$user->id || !$user->authorise('core.create', 'com_ttc_hallenbuchung')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
        $item = (object) get_object_vars($table);
        if (!$id) {
            $item->state = 1;
            $item->datum = Factory::getDate()->format('Y-m-d');
            $item->slot = '1';
            $item->paarung = $user->name;
        }
        return $item;
    }

    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm('com_ttc_hallenbuchung.tttraining', 'tttrainingform', ['control' => 'jform', 'load_data' => $loadData]);
    }

    protected function loadFormData()
    {
        $item = $this->getItem(); // Always authorize the actual record before showing cached data.
        $data = Factory::getApplication()->getUserState('com_ttc_hallenbuchung.edit.tttraining.data', []);
        return $data && (int) ($data['id'] ?? 0) === (int) $item->id ? $data : $item;
    }

    public function save($data)
    {
        $id = (int) ($data['id'] ?? 0);
        $item = $this->getItem($id); // Includes ownership/creation checks.
        $table = $this->getTable();
        if ($id && !$table->load($id)) { return false; }
        // Frontend input cannot overwrite ownership, ordering or publication status.
        $values = ['id' => $id, 'datum' => $data['datum'] ?? '', 'slot' => $data['slot'] ?? '', 'paarung' => $data['paarung'] ?? '', 'state' => (int) $item->state];
        if (!$table->save($values)) { $this->setError($table->getError()); return false; }
        return $table->id;
    }

    public function delete($id)
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->id || !$user->authorise('core.delete', 'com_ttc_hallenbuchung')) { throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403); }
        $table = $this->getTable();
        if (!$table->load((int) $id)) { throw new \RuntimeException(Text::_('COM_TTC_HALLENBUCHUNG_TT_NOT_FOUND'), 404); }
        return $table->delete((int) $id);
    }
}
