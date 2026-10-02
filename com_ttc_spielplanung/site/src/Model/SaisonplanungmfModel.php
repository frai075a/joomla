<?php
namespace Ttc\Component\Spielplanung\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Ttc\Component\Spielplanung\Administrator\Repository\SpielplanungRepository;

class SaisonplanungmfModel extends SpielplanModel
{
    private $playerId = 0;

    public function setPlayerId($playerId)
    {
        $this->playerId = (int) $playerId;
    }

    /** Resolve access from the current roster on every request, including saves. */
    public function getCaptainTeam()
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->id) {
            return null;
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('rk.category_id, rk.sort_order')
            ->from($db->quoteName('#__ttc_mmb') . ' AS mmb')
            ->innerJoin($db->quoteName('#__ttc_relevante_kategorien') . ' AS rk ON rk.category_id = mmb.category_id')
            ->where('mmb.user_id = ' . (int) $user->id)
            ->where('mmb.is_captain = 1')
            ->where('mmb.state = 1')
            ->where('rk.state = 1');
        $db->setQuery($query);
        return $db->loadObject() ?: null;
    }

    /** Eligible players belong to the same or a numerically higher active team. */
    public function getPlayers()
    {
        $team = $this->getCaptainTeam();
        if (!$team) {
            return array();
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('u.id, u.name, c.title AS team_title, rk.sort_order')
            ->from($db->quoteName('#__ttc_mmb') . ' AS mmb')
            ->innerJoin($db->quoteName('#__users') . ' AS u ON u.id = mmb.user_id')
            ->innerJoin($db->quoteName('#__ttc_relevante_kategorien') . ' AS rk ON rk.category_id = mmb.category_id')
            ->innerJoin($db->quoteName('#__categories') . ' AS c ON c.id = rk.category_id')
            ->where('mmb.state = 1')
            ->where('rk.state = 1')
            ->where('u.block = 0')
            ->where('rk.sort_order >= ' . (int) $team->sort_order)
            ->order('rk.sort_order ASC, mmb.position ASC, u.name ASC');
        $db->setQuery($query);
        return $db->loadAssocList('id');
    }

    protected function getAvailabilityPlayer()
    {
        $players = $this->getPlayers();
        return isset($players[$this->playerId]) ? (object) $players[$this->playerId] : null;
    }

    public function getGames($onlyFuture = true, $vorrundeOnly = false)
    {
        $player = $this->getAvailabilityPlayer();
        if (!$player) {
            return array();
        }
        $db = $this->getDatabase();
        $query = (new SpielplanungRepository($db))->createUserGamesQuery(Factory::getApplication()->getIdentity()->id, $onlyFuture, $vorrundeOnly)
            ->select('sp.mannschaft, rk.sort_order, spl.status')
            ->leftJoin($db->quoteName('#__ttc_spielplanung') . ' AS spl ON spl.game_id = sp.id AND spl.user_id = ' . (int) $player->id);
        $db->setQuery($query);
        return $db->loadAssocList('game_id');
    }

    public function getGameDetails(array $gameIds)
    {
        return $this->getAvailabilityPlayer() ? parent::getGameDetails($gameIds) : array();
    }

    public function getConfirmedPlayers(array $gameIds)
    {
        $games = $this->getGameDetails($gameIds);
        return (new SpielplanungRepository($this->getDatabase()))->getConfirmedPlayers(array_keys($games));
    }

    /**
     * Captain-managed changes must not trigger captain notification mails —
     * only the player's own "Meine Spiele" changes do (see parent SpielplanModel::saveAndNotify()).
     *
     * @return array|false ['warnings' => []], or false when saving failed.
     */
    public function saveAndNotify(array $statuses)
    {
        $changes = $this->saveGames($statuses);
        if ($changes === false) {
            return false;
        }

        return array('warnings' => array());
    }
}
