<?php
namespace Ttc\Component\Spielplanung\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Ttc\Component\Spielplanung\Site\Service\CaptainNotificationService;
use Ttc\Component\Spielplanung\Administrator\Repository\SpielplanungRepository;

class SpielplanModel extends BaseDatabaseModel
{
    public function getGames($onlyFuture = true, $vorrundeOnly = false)
    {
        $user = Factory::getApplication()->getIdentity();
        if ($user->id == 0) {
            return array();
        }

        $db = $this->getDatabase();
        $repository = new SpielplanungRepository($db);
        $query = $repository->createUserGamesQuery($user->id, $onlyFuture, $vorrundeOnly)
            ->select('sp.mannschaft, rk.sort_order')
            ->select('u.username, u.name, u.email')
            ->select('spl.id AS spielplanung_id, spl.status')
            ->innerJoin($db->quoteName('#__users') . ' AS u ON u.id = mmb.user_id')
            ->leftJoin($db->quoteName('#__ttc_spielplanung') . ' AS spl ON spl.user_id = ' . (int) $user->id . ' AND spl.game_id = sp.id');

        $db->setQuery($query);
        return $db->loadAssocList();
    }

    protected function getAvailabilityPlayer()
    {
        return Factory::getApplication()->getIdentity();
    }

    /**
     * Application operation: commit availability first, then notify captains.
     * Mail failures are warnings and must not turn a successful save into a failure.
     *
     * @return array|false ['warnings' => string[]], or false when saving failed.
     */
    public function saveAndNotify(array $statuses)
    {
        $player = $this->getAvailabilityPlayer();
        if (!$player || !$player->id) {
            return false;
        }
        $changes = $this->saveGames($statuses);
        if ($changes === false)
        {
            return false;
        }

        $notifier = new CaptainNotificationService();
        return array('warnings' => $notifier->notify($this, $player, $changes));
    }

    public function saveGame($gameId, $status)
    {
        return $this->saveGames(array($gameId => $status));
    }

    /**
     * Saves the given game statuses for the authorized availability player and returns the games whose status actually changed.
     *
     * @return array|false  List of changed games (game details + old_status/new_status), or false on failure.
     */
    public function saveGames(array $statuses)
    {
        $user = Factory::getApplication()->getIdentity();
        if ($user->id == 0 || empty($statuses)) {
            return false;
        }

        $player = $this->getAvailabilityPlayer();
        if (!$player || !$player->id) {
            return false;
        }

        $db = $this->getDatabase();
        $now = Factory::getDate()->toSql();

        // Validate the whole batch before writing any row, including direct model calls.
        foreach ($statuses as $gameId => $status) {
            if ((!is_int($gameId) && !is_string($gameId))
                || !preg_match('/^[1-9][0-9]*$/D', (string) $gameId)
                || filter_var($gameId, FILTER_VALIDATE_INT) === false
                || !in_array($status, array(0, 1, '0', '1'), true)) {
                return false;
            }
        }

        $changes = array();
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;

            $gameDetails = $this->getGameDetails(array_keys($statuses));
            if (array_diff(array_keys($statuses), array_keys($gameDetails))) {
                $db->transactionRollback();
                return false;
            }

            foreach ($statuses as $gameId => $status) {
                $gameId = (int) $gameId;
                $status = (int) $status;

                $query = $db->getQuery(true)
                    ->select('id, status')
                    ->from($db->quoteName('#__ttc_spielplanung'))
                    ->where('user_id = ' . (int) $player->id)
                    ->where('game_id = ' . $gameId);
                $db->setQuery($query);
                $existing = $db->loadObject();

                $oldStatus = $existing ? (int) $existing->status : 1;

                if ($existing) {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__ttc_spielplanung'))
                        ->set('status = ' . $status)
                        ->set('modified = ' . $db->quote($now))
                        ->set('modified_by = ' . (int) $user->id)
                        ->where('id = ' . (int) $existing->id);
                    $db->setQuery($query);
                    $db->execute();
                } else {
                    $query = $db->getQuery(true)
                        ->insert($db->quoteName('#__ttc_spielplanung'))
                        ->columns(array('user_id', 'game_id', 'status', 'state', 'created', 'created_by'))
                        ->values((int) $player->id . ', ' . $gameId . ', ' . $status . ', 1, ' . $db->quote($now) . ', ' . (int) $user->id);
                    $db->setQuery($query);
                    $db->execute();
                }

                if ($oldStatus !== $status && isset($gameDetails[$gameId])) {
                    $changes[] = $gameDetails[$gameId] + array(
                        'old_status' => $oldStatus,
                        'new_status' => $status,
                    );
                }
            }

            $db->transactionCommit();
            return $changes;
        } catch (\Exception $e) {
            if ($transactionStarted) {
                $db->transactionRollback();
            }
            return false;
        }
    }

    /**
     * Loads only games assigned to the current user's active team, keyed by game id.
     */
    public function getGameDetails(array $gameIds)
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->id) {
            return array();
        }

        $gameIds = array_filter(array_map('intval', $gameIds));
        if (empty($gameIds)) {
            return array();
        }

        $db = $this->getDatabase();
        $repository = new SpielplanungRepository($db);
        $query = $repository->createUserGamesQuery($user->id)
            ->where('sp.id IN (' . implode(',', $gameIds) . ')');

        $db->setQuery($query);
        return $db->loadAssocList('game_id') ?: array();
    }

    /**
     * Returns the captain's user id/name/email for the given category, or null if none is set.
     */
    public function getCaptain($categoryId)
    {
        $categoryId = (int) $categoryId;
        if (!$categoryId) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('u.id, u.name, u.email')
            ->from($db->quoteName('#__ttc_mmb') . ' AS mmb')
            ->innerJoin($db->quoteName('#__users') . ' AS u ON u.id = mmb.user_id')
            ->where('mmb.category_id = ' . $categoryId)
            ->where('mmb.is_captain = 1')
            ->where('mmb.state = 1');
        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }
}
