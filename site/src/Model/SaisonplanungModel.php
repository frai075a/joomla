<?php
namespace Ttc\Component\Spielplanung\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Ttc\Component\Spielplanung\Administrator\Repository\SpielplanungRepository;

class SaisonplanungModel extends BaseDatabaseModel
{
    /**
     * Returns all games of the categories the current user is assigned to in #__ttc_mmb.
     */
    public function getGames($onlyFuture = true, $vorrundeOnly = false)
    {
        $user = Factory::getApplication()->getIdentity();
        if ($user->id == 0) {
            return array();
        }

        $db = $this->getDatabase();
        $repository = new SpielplanungRepository($db);
        $query = $repository->createUserGamesQuery($user->id, $onlyFuture, $vorrundeOnly)
            ->select('sp.mannschaft, rk.sort_order');

        $db->setQuery($query);
        return $db->loadAssocList('game_id');
    }

    /**
     * Loads the confirmed (Zusage) players for each of the given games, keyed by game id, ordered by each
     * player's position in the team roster (#__ttc_mmb) for the game's category.
     *
     * @return array  [game_id => [name, name, ...]]
     */
    public function getConfirmedPlayers(array $gameIds)
    {
        return (new SpielplanungRepository($this->getDatabase()))->getConfirmedPlayers($gameIds);
    }
}
