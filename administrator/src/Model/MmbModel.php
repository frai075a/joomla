<?php
namespace Ttc\Component\Spielplanung\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Ttc\Component\Spielplanung\Administrator\Repository\SpielplanungRepository;

class MmbModel extends ListModel
{
    public function __construct($config = array(), ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id', 'u.id',
                'username', 'u.username',
                'name', 'u.name',
                'category_id', 'm.category_id',
                'position', 'm.position',
            );
        }
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $search = Factory::getApplication()->getUserStateFromRequest(
            'com_ttc_spielplanung.mmb.search', 'mmb_search', '', 'string'
        );
        $this->setState('filter.search', trim((string) $search));
        parent::populateState('u.id', 'ASC');
    }

    protected function getStoreId($id = '')
    {
        return parent::getStoreId($id . ':' . (string) $this->getState('filter.search'));
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select('u.id, u.username, u.name, u.email')
            ->select('m.id AS mmb_id, m.category_id, m.position, m.is_captain')
            ->select('cat.title AS category_title')
            ->from($db->quoteName('#__users') . ' AS u')
            ->leftJoin($db->quoteName('#__ttc_mmb') . ' AS m ON m.user_id = u.id')
            ->leftJoin($db->quoteName('#__ttc_relevante_kategorien') . ' AS c ON c.category_id = m.category_id')
			->leftJoin($db->quoteName('#__categories') . ' AS cat ON cat.id = m.category_id')
            ->order('CASE WHEN COALESCE(m.category_id, 0) > 0 THEN 0 ELSE 1 END ASC, CASE WHEN c.sort_order IS NULL THEN 1 ELSE 0 END ASC, c.sort_order ASC, m.category_id ASC, CASE WHEN COALESCE(m.position, 0) > 0 THEN 0 ELSE 1 END ASC, m.position ASC, u.id ASC');

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            // Literal substring search: quotes and SQL wildcard characters stay data.
            $query->where('INSTR(LOWER(u.name), LOWER(' . $db->quote($search) . ')) > 0');
        }

        return $query;
    }

    public function getItems()
    {
        return parent::getItems();
    }

    public function getRelevantCategories()
    {
        return (new SpielplanungRepository($this->getDatabase()))->getRelevantCategories();
    }

    /**
     * Save the submitted roster rows atomically, including captain assignment.
     */
    public function saveRows(array $data)
    {
        $this->assertCanManage();
        $db = $this->getDatabase();
        $user = Factory::getApplication()->getIdentity();
        $now = Factory::getDate()->toSql();
        $db->transactionStart();

        try
        {
            foreach ($data as $userId => $itemData)
            {
                $userId = (int) $userId;
                $categoryId = isset($itemData['category_id']) ? (int) $itemData['category_id'] : null;
                $position = isset($itemData['position']) ? (int) $itemData['position'] : null;
                $isCaptain = !empty($itemData['is_captain']) ? 1 : 0;

                if ($categoryId && $position && $position < 1)
                {
                    $position = null;
                }

                if (!$categoryId)
                {
                    $position = null;
                    $isCaptain = 0;
                }

                $query = $db->getQuery(true)
                    ->select('id')
                    ->from($db->quoteName('#__ttc_mmb'))
                    ->where('user_id = ' . $userId);
                $db->setQuery($query);
                $mmbId = $db->loadResult();

                if ($isCaptain && $categoryId)
                {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__ttc_mmb'))
                        ->set('is_captain = 0')
                        ->where('category_id = ' . $categoryId)
                        ->where('user_id != ' . $userId);
                    $db->setQuery($query);
                    $db->execute();
                }

                if ($mmbId)
                {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__ttc_mmb'))
                        ->set('category_id = ' . ($categoryId ?: 'NULL'))
                        ->set('position = ' . ($position ?: 'NULL'))
                        ->set('is_captain = ' . $isCaptain)
                        ->set('modified = ' . $db->quote($now))
                        ->set('modified_by = ' . (int) $user->id)
                        ->where('id = ' . (int) $mmbId);
                }
                else
                {
                    $query = $db->getQuery(true)
                        ->insert($db->quoteName('#__ttc_mmb'))
                        ->columns(array('user_id', 'category_id', 'position', 'is_captain', 'state', 'created', 'created_by'))
                        ->values(
                            $userId . ', ' . ($categoryId ?: 'NULL') . ', ' . ($position ?: 'NULL') . ', ' . $isCaptain . ', 1, ' . $db->quote($now) . ', ' . (int) $user->id
                        );
                }

                $db->setQuery($query);
                $db->execute();
            }

            $db->transactionCommit();
            return true;
        }
        catch (\Exception $e)
        {
            $db->transactionRollback();
            throw $e;
        }
    }

    /**
     * Convert an up/down command into a roster position change.
     */
    public function movePosition($userId, $categoryId, $direction)
    {
        $this->assertCanManage();
        if (!in_array($direction, array('up', 'down'), true))
        {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('position')
            ->from($db->quoteName('#__ttc_mmb'))
            ->where('user_id = ' . (int) $userId);
        $db->setQuery($query);
        $currentPosition = $db->loadResult() ?: 1;
        $newPosition = $direction === 'up' ? $currentPosition - 1 : $currentPosition + 1;

        if ($newPosition < 1 || $newPosition > 99)
        {
            $newPosition = $currentPosition;
        }

        return $this->updatePosition($userId, $categoryId, $newPosition);
    }

    private function assertCanManage()
    {
        if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_ttc_spielplanung'))
        {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_INVALID_ACTION'));
        }
    }

    public function updatePosition($userId, $categoryId, $newPosition)
    {
        $this->assertCanManage();
        $db = $this->getDatabase();

        if (!$categoryId || $newPosition < 1 || $newPosition > 99)
        {
            return false;
        }

        $db->transactionStart();

        try
        {
            $query = $db->getQuery(true)
                ->select('position')
                ->from($db->quoteName('#__ttc_mmb'))
                ->where('user_id = ' . (int) $userId);
            $db->setQuery($query);
            $oldPosition = $db->loadResult();

            if ($newPosition != $oldPosition)
            {
                $this->shiftPositions($categoryId, $oldPosition, $newPosition);
            }

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__ttc_mmb'))
                ->set('position = ' . (int) $newPosition)
                ->where('user_id = ' . (int) $userId);
            $db->setQuery($query);
            $db->execute();

            $db->transactionCommit();
            return true;
        }
        catch (\Exception $e)
        {
            $db->transactionRollback();
            return false;
        }
    }

    private function shiftPositions($categoryId, $oldPosition, $newPosition)
    {
        $db = $this->getDatabase();

        if ($newPosition < $oldPosition)
        {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__ttc_mmb'))
                ->set('position = position + 1')
                ->where('category_id = ' . (int) $categoryId)
                ->where('position >= ' . (int) $newPosition)
                ->where('position < ' . (int) $oldPosition);
        }
        else
        {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__ttc_mmb'))
                ->set('position = position - 1')
                ->where('category_id = ' . (int) $categoryId)
                ->where('position > ' . (int) $oldPosition)
                ->where('position <= ' . (int) $newPosition);
        }

        $db->setQuery($query);
        $db->execute();
    }
}
