<?php
namespace Ttc\Component\Spielplanung\Administrator\Repository;

defined('_JEXEC') or die;

/**
 * Shared read queries for site and administrator models.
 * No application/user lookup here: callers explicitly choose the user-scoped query.
 */
class SpielplanungRepository
{
    /** @var \Joomla\Database\DatabaseInterface */
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Common match details. Unscoped; intended for backend lists or further restriction.
     *
     * @return \Joomla\Database\DatabaseQuery
     */
    public function createGamesQuery()
    {
        $db = $this->db;

        return $db->getQuery(true)
            ->select(
                'sp.id AS game_id, sp.datum AS spieldatum, sp.uhrzeit, sp.ort AS sporthalle,'
                . ' CASE WHEN sp.heimmannschaft = ' . $db->quote('TTC Nordend Frankfurt')
                . ' THEN sp.auswaertsmannschaft ELSE sp.heimmannschaft END AS gegner'
            )
            ->select('rk.category_id')
            ->from($db->quoteName('#__ttc_spielplan') . ' AS sp')
            ->innerJoin($db->quoteName('#__ttc_relevante_kategorien') . ' AS rk ON rk.sort_order = sp.mannschaft');
    }

    /**
     * Matches of the user's active team, used for both display and save validation.
     * A missing user never falls back to the unrestricted backend query.
     *
     * @return \Joomla\Database\DatabaseQuery
     */
    public function createUserGamesQuery($userId, $onlyFuture = false, $vorrundeOnly = false)
    {
        $db = $this->db;
        $query = $this->createGamesQuery()
            ->innerJoin($db->quoteName('#__ttc_mmb') . ' AS mmb ON mmb.category_id = rk.category_id')
            ->where('mmb.user_id = ' . (int) $userId)
            ->where('mmb.state = 1')
            ->where('rk.state = 1')
            ->order('sp.datum ASC, sp.uhrzeit ASC');

        if ((int) $userId <= 0)
        {
            $query->where('1 = 0');
        }

        if ($onlyFuture)
        {
            $query->where('sp.datum >= CURDATE()');
        }

        if ($vorrundeOnly)
        {
            $query->where('sp.datum <= (SELECT MAX(' . $db->quoteName('datum') . ') FROM ' . $db->quoteName('#__ttc_hinrueckgrenze') . ')');
        }

        return $query;
    }

    /**
     * Confirmed players for the supplied games, ordered by roster position and name.
     * Callers supply the game IDs from their already scoped list.
     *
     * @return array [game_id => [name, ...]]
     */
    public function getConfirmedPlayers(array $gameIds)
    {
        $gameIds = array_filter(array_map('intval', $gameIds));
        if (empty($gameIds))
        {
            return array();
        }

        $db = $this->db;
        $query = $db->getQuery(true)
            ->select('spl.game_id, u.name, mmb.position')
            ->from($db->quoteName('#__ttc_spielplanung') . ' AS spl')
            ->innerJoin($db->quoteName('#__users') . ' AS u ON u.id = spl.user_id')
            ->innerJoin($db->quoteName('#__ttc_spielplan') . ' AS sp ON sp.id = spl.game_id')
            ->innerJoin($db->quoteName('#__ttc_relevante_kategorien') . ' AS rk ON rk.sort_order = sp.mannschaft')
            ->leftJoin($db->quoteName('#__ttc_mmb') . ' AS mmb ON mmb.user_id = spl.user_id AND mmb.category_id = rk.category_id')
            ->where('spl.game_id IN (' . implode(',', $gameIds) . ')')
            ->where('spl.status = 1')
            ->order('rk.sort_order, mmb.position IS NULL, mmb.position ASC, u.name ASC');

        $db->setQuery($query);
        $rows = $db->loadAssocList();

        $players = array();
        foreach ($rows as $row)
        {
            $players[(int) $row['game_id']][] = $row['name'];
        }

        return $players;
    }

    /** @return array Active categories keyed by category_id. */
    public function getRelevantCategories()
    {
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select('rk.category_id, c.title')
            ->from($db->quoteName('#__ttc_relevante_kategorien') . ' AS rk')
            ->innerJoin($db->quoteName('#__categories') . ' AS c ON c.id = rk.category_id')
            ->where('rk.state = 1')
            ->order('c.title ASC');

        $db->setQuery($query);
        return $db->loadAssocList('category_id');
    }
}
