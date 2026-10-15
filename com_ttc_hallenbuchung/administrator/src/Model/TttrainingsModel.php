<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

class TttrainingsModel extends ListModel
{
    protected $option = 'com_ttc_hallenbuchung';

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = ['id', 'a.id', 'datum', 'a.datum', 'slot', 'a.slot', 'paarung', 'a.paarung', 'state', 'a.state'];
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState('a.datum', 'ASC');
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
    }

    protected function getStoreId($id = '')
    {
        return parent::getStoreId($id . ':' . $this->getState('filter.search', ''));
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)->select('a.*')->from($db->quoteName('#__ttc_coronaorga_', 'a'));
        $search = (string) $this->getState('filter.search', '');
        if ($search !== '') {
            $query->where('a.paarung LIKE ' . $db->quote('%' . $db->escape($search, true) . '%', false));
        }
        $order = (string) $this->getState('list.ordering', 'a.datum');
        if (!in_array($order, ['a.id', 'a.datum', 'a.slot', 'a.paarung', 'a.state'], true)) { $order = 'a.datum'; }
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        return $query->order($db->quoteName($order) . ' ' . $direction)->order('a.slot ASC')->order('a.id ASC');
    }
}
