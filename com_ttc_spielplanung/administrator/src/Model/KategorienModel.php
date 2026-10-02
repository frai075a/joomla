<?php
namespace Ttc\Component\Spielplanung\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class KategorienModel extends ListModel
{
    public function __construct($config = array(), ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id', 'c.id',
                'title', 'c.title',
                'level', 'c.level',
                'lft', 'c.lft',
            );
        }
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState('c.lft', 'ASC');
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select('c.id, c.title, c.level, c.lft, c.rgt')
            ->select('IF(rk.id IS NOT NULL, 1, 0) AS is_selected')
            ->select('rk.sort_order')
            ->from($db->quoteName('#__categories') . ' AS c')
            ->leftJoin($db->quoteName('#__ttc_relevante_kategorien') . ' AS rk ON rk.category_id = c.id')
            ->where('c.extension = ' . $db->quote('com_content'))
            ->order('c.lft ASC');

        return $query;
    }

    public function getItems()
    {
        return parent::getItems();
    }

    /**
     * Save only explicitly submitted rows; categories on other pages remain unchanged.
     */
    public function saveSelection(array $data)
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('core.manage', 'com_ttc_spielplanung'))
        {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_INVALID_ACTION'));
        }

        if (!$data)
        {
            throw new \InvalidArgumentException(Text::_('COM_TTC_SPIELPLANUNG_INVALID_CATEGORIES'));
        }

        $rows = array();
        foreach ($data as $categoryId => $item)
        {
            if (!preg_match('/^[1-9][0-9]*$/D', (string) $categoryId)
                || filter_var($categoryId, FILTER_VALIDATE_INT) === false
                || !is_array($item)
                || !isset($item['checked'])
                || !in_array($item['checked'], array(0, 1, '0', '1'), true))
            {
                throw new \InvalidArgumentException(Text::_('COM_TTC_SPIELPLANUNG_INVALID_CATEGORIES'));
            }

            $sortOrder = null;
            if ((int) $item['checked'] === 1)
            {
                $value = $item['sort_order'] ?? null;
                if ((!is_int($value) && !is_string($value))
                    || !preg_match('/^[1-9][0-9]?$/D', (string) $value))
                {
                    throw new \InvalidArgumentException(Text::_('COM_TTC_SPIELPLANUNG_ORDERING_REQUIRED'));
                }
                $sortOrder = (int) $value;
            }

            $rows[(int) $categoryId] = $sortOrder;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__categories'))
            ->where('extension = ' . $db->quote('com_content'))
            ->where('id IN (' . implode(',', array_keys($rows)) . ')');
        $db->setQuery($query);
        if (array_diff(array_keys($rows), $db->loadColumn()))
        {
            throw new \InvalidArgumentException(Text::_('COM_TTC_SPIELPLANUNG_INVALID_CATEGORIES'));
        }

        $now = Factory::getDate()->toSql();
        $db->transactionStart();

        try
        {
            $query = $db->getQuery(true)
                ->select('category_id')
                ->from($db->quoteName('#__ttc_relevante_kategorien'))
                ->where('category_id IN (' . implode(',', array_keys($rows)) . ')');
            $db->setQuery($query);
            $existing = array_flip($db->loadColumn());

            foreach ($rows as $categoryId => $sortOrder)
            {
                $query = $db->getQuery(true);
                if ($sortOrder === null)
                {
                    $query->delete($db->quoteName('#__ttc_relevante_kategorien'))
                        ->where('category_id = ' . $categoryId);
                }
                elseif (isset($existing[$categoryId]))
                {
                    $query->update($db->quoteName('#__ttc_relevante_kategorien'))
                        ->set('sort_order = ' . $sortOrder)
                        ->set('state = 1')
                        ->set('modified = ' . $db->quote($now))
                        ->set('modified_by = ' . (int) $user->id)
                        ->where('category_id = ' . $categoryId);
                }
                else
                {
                    $query->insert($db->quoteName('#__ttc_relevante_kategorien'))
                        ->columns(array('category_id', 'sort_order', 'state', 'created', 'created_by'))
                        ->values($categoryId . ', ' . $sortOrder . ', 1, ' . $db->quote($now) . ', ' . (int) $user->id);
                }
                $db->setQuery($query);
                $db->execute();
            }

            $db->transactionCommit();
        }
        catch (\Exception $e)
        {
            $db->transactionRollback();
            throw $e;
        }
    }

    public function getSelectedIds()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('category_id')
            ->from($db->quoteName('#__ttc_relevante_kategorien'))
            ->where('state = 1');

        $db->setQuery($query);
        return $db->loadColumn();
    }
}
