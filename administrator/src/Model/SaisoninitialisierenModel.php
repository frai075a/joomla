<?php
namespace Ttc\Component\Spielplanung\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SaisoninitialisierenModel extends BaseDatabaseModel
{
    protected function getNow(): \DateTimeImmutable
    {
        $timezone = new \DateTimeZone(Factory::getApplication()->get('offset', 'Europe/Berlin'));
        return new \DateTimeImmutable('now', $timezone);
    }

    public function getSeason(): array
    {
        $year = (int) $this->getNow()->format('Y');
        $date = $year . '-12-31';
        return ['year' => $year, 'nextYear' => $year + 1, 'date' => $date, 'exists' => $this->hasDate($date)];
    }

    private function hasDate(string $date): bool
    {
        $db = $this->getDatabase();
        $db->setQuery($db->getQuery(true)->select('COUNT(*)')
            ->from($db->quoteName('#__ttc_hinrueckgrenze'))
            ->where($db->quoteName('datum') . ' = ' . $db->quote($date)));
        return (int) $db->loadResult() > 0;
    }

    /** True when inserted, false when this season was already initialized. */
    public function initialize(): bool
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('core.manage', 'com_ttc_spielplanung')
            || !$user->authorise('core.create', 'com_ttc_spielplanung')) {
            throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_SEASON_DENIED'), 403);
        }

        // Always derive the date server-side, including requests from an old browser tab.
        $date = $this->getNow()->format('Y') . '-12-31';
        $db = $this->getDatabase();
        // Serialize this action without altering the externally owned table's schema.
        // https://dev.mysql.com/doc/refman/8.4/en/locking-functions.html
        $lock = $db->quote('com_ttc_spielplanung.saison.' . $date);
        $db->setQuery('SELECT GET_LOCK(' . $lock . ', 5)');
        if ((int) $db->loadResult() !== 1) {
            throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_SEASON_FAILED'));
        }
        try {
            if ($this->hasDate($date)) {
                return false;
            }
            $db->setQuery($db->getQuery(true)->insert($db->quoteName('#__ttc_hinrueckgrenze'))
                ->columns([$db->quoteName('datum')])->values($db->quote($date)));
            $db->execute();
            return true;
        } finally {
            $db->setQuery('SELECT RELEASE_LOCK(' . $lock . ')');
            $db->loadResult();
        }
    }
}
