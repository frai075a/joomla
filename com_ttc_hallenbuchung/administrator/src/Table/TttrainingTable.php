<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

/** Uses the original training table without schema changes. */
class TttrainingTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__ttc_coronaorga_', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function check()
    {
        $day = substr((string) $this->datum, 0, 10);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $day);
        if (!$date || $date->format('Y-m-d') !== $day || !in_array((string) $this->slot, ['1', '2'], true)
            || trim((string) $this->paarung) === '' || mb_strlen((string) $this->paarung) > 100
            || !in_array((int) $this->state, [-2, 0, 1, 2], true)) {
            $this->setError(Text::_('COM_TTC_HALLENBUCHUNG_TT_INVALID'));
            return false;
        }
        $this->datum = $day . ' 00:00:00';
        $this->paarung = trim((string) $this->paarung);
        return true;
    }

    public function store($updateNulls = false)
    {
        $db = $this->getDatabase();
        $user = Factory::getApplication()->getIdentity();
        if (!$this->id) {
            $this->created_by = $user->id;
            $this->ordering = (int) $this->getNextOrder();
        }
        $this->modified_by = $user->id;
        // Serialize bookings across MySQL requests without adding a table or column.
        $mysql = in_array($db->name, ['mysqli', 'mysql'], true);
        $lock = 'tttraining:' . sha1($db->getPrefix());
        if ($mysql) {
            $db->setQuery('SELECT GET_LOCK(' . $db->quote($lock) . ', 10)');
            if ((int) $db->loadResult() !== 1) {
                $this->setError(Text::_('COM_TTC_HALLENBUCHUNG_TT_BUSY'));
                return false;
            }
        }
        try {
            if ((int) $this->state === 1) {
                $day = substr((string) $this->datum, 0, 10);
                $nextDay = (new \DateTimeImmutable($day))->modify('+1 day')->format('Y-m-d');
                $query = $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__ttc_coronaorga_'))
                    ->where('datum >= ' . $db->quote($day . ' 00:00:00'))
                    ->where('datum < ' . $db->quote($nextDay . ' 00:00:00'))
                    ->where('slot = ' . $db->quote((string) $this->slot))
                    ->where('state = 1')->where('id <> ' . (int) $this->id);
                $db->setQuery($query);
                if ((int) $db->loadResult() >= 5) {
                    $this->setError(Text::_('COM_TTC_HALLENBUCHUNG_TT_FULL'));
                    return false;
                }
            }
            return parent::store($updateNulls);
        } finally {
            if ($mysql) {
                $db->setQuery('SELECT RELEASE_LOCK(' . $db->quote($lock) . ')')->loadResult();
            }
        }
    }

    public function publish($pks = null, $state = 1, $userId = 0)
    {
        foreach ((array) ($pks ?? $this->id) as $id) {
            if (!$this->load((int) $id)) { return false; }
            $this->state = (int) $state;
            if (!$this->check() || !$this->store()) { return false; }
        }
        return true;
    }
}
