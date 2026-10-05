<?php
namespace Ttc\Component\Spielplanung\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Ttc\Component\Spielplanung\Administrator\Repository\SpielplanungRepository;

class SaisonplanungModel extends ListModel
{
    public function __construct($config = array(), ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'game_id', 'sp.id',
                'spieldatum', 'sp.datum',
                'category_id', 'rk.category_id',
            );
        }
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();

        $categoryId = $app->getUserStateFromRequest(
            $this->context . '.filter.category_id',
            'filter_category_id',
            '',
            'int'
        );
        $this->setState('filter.category_id', $categoryId);

        parent::populateState('sp.datum', 'ASC');
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $repository = new SpielplanungRepository($db);
        $query = $repository->createGamesQuery()
            ->select('sp.mannschaft, cat.title AS category_title')
            ->leftJoin($db->quoteName('#__categories') . ' AS cat ON cat.id = rk.category_id')
            ->order('sp.datum ASC, sp.uhrzeit ASC');

        $categoryId = (int) $this->getState('filter.category_id');
        if ($categoryId > 0)
        {
            $query->where('rk.category_id = ' . $categoryId);
        }

        return $query;
    }

    public function getItems()
    {
        return parent::getItems();
    }

    /**
     * Returns the relevant categories (teams) available to filter by, keyed by category_id.
     */
    public function getRelevantCategories()
    {
        return (new SpielplanungRepository($this->getDatabase()))->getRelevantCategories();
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
