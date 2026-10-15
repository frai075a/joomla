<?php
namespace Ttc\Component\Spielplanung\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

class EintraegeModel extends ListModel
{

    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $this->setState('filter.player_id', max(0, (int) $app->getUserStateFromRequest(
            $this->context . '.player_id', 'entries_player', 0, 'int'
        )));
        $sort = $app->getUserStateFromRequest($this->context . '.sort', 'entries_sort', 'player', 'cmd');
        $direction = strtoupper($app->getUserStateFromRequest($this->context . '.direction', 'entries_direction', 'ASC', 'cmd'));
        $this->setState('filter.sort', in_array($sort, ['player', 'match', 'date'], true) ? $sort : 'player');
        $this->setState('filter.direction', $direction === 'DESC' ? 'DESC' : 'ASC');
        parent::populateState('p.id', 'DESC');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . (int) $this->getState('filter.player_id');
        $id .= ':' . $this->getState('filter.sort') . ':' . $this->getState('filter.direction');
        return parent::getStoreId($id);
    }

    public function getPlayers()
    {
        $db = $this->getDatabase();
        $db->setQuery($db->getQuery(true)
            ->select('DISTINCT p.user_id, u.name AS player_name')
            ->from($db->quoteName('#__ttc_spielplanung') . ' AS p')
            ->leftJoin($db->quoteName('#__users') . ' AS u ON u.id = p.user_id')
            ->order('u.name ASC, p.user_id ASC'));
        return $db->loadAssocList();
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        // LEFT JOIN keeps orphaned entries visible and removable.
        $query = $db->getQuery(true)
            ->select('p.*, u.name AS player_name, sp.heimmannschaft, sp.h_nummer, sp.auswaertsmannschaft, sp.a_nummer, sp.datum AS game_date, creator.name AS creator_name, modifier.name AS modifier_name')
            ->from($db->quoteName('#__ttc_spielplanung') . ' AS p')
            ->leftJoin($db->quoteName('#__users') . ' AS u ON u.id = p.user_id')
            ->leftJoin($db->quoteName('#__ttc_spielplan') . ' AS sp ON sp.id = p.game_id')
            ->leftJoin($db->quoteName('#__users') . ' AS creator ON creator.id = p.created_by')

            ->leftJoin($db->quoteName('#__users') . ' AS modifier ON modifier.id = p.modified_by');

        $playerId = (int) $this->getState('filter.player_id');
        if ($playerId > 0) {
            $query->where('p.user_id = ' . $playerId);
        }

        // SQL identifiers and direction come exclusively from these allowlists.
        $sortColumns = [
            'player' => ['u.name', 'p.user_id'],
            'match' => ['sp.heimmannschaft', 'sp.h_nummer', 'sp.auswaertsmannschaft', 'sp.a_nummer'],
            'date' => ['sp.datum'],
        ];
        $sort = (string) $this->getState('filter.sort');
        $direction = $this->getState('filter.direction') === 'DESC' ? 'DESC' : 'ASC';
        $columns = $sortColumns[$sort] ?? $sortColumns['player'];
        $order = array_map(static function ($column) use ($direction) { return $column . ' ' . $direction; }, $columns);
        $order[] = 'p.id DESC';
        return $query->order(implode(', ', $order));

    }

    public function removeEntry($id)
    {
        $this->assertCanDelete();
        if ((!is_int($id) && !is_string($id))
            || !preg_match('/^[1-9][0-9]*$/D', (string) $id)
            || filter_var($id, FILTER_VALIDATE_INT) === false) {
            throw new \InvalidArgumentException(Text::_('COM_TTC_SPIELPLANUNG_INVALID_ENTRY'));
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)->delete($db->quoteName('#__ttc_spielplanung'))
            ->where($db->quoteName('id') . ' = ' . (int) $id);
        $db->setQuery($query);
        $db->execute();
    }

    public function removeAllEntries()
    {
        $this->assertCanDelete();
        $db = $this->getDatabase();
        $db->setQuery($db->getQuery(true)->delete($db->quoteName('#__ttc_spielplanung')));
        $db->execute();
    }

    private function assertCanDelete()
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('core.manage', 'com_ttc_spielplanung')
            || !$user->authorise('core.delete', 'com_ttc_spielplanung')) {
            throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_DELETE_DENIED'), 403);
        }
    }
}
