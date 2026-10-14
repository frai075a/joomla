<?php
/**
 * @version    CVS: 1.0.6
 * @package    Com_Spielplan
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Spielplanung\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use Ttc\Component\Spielplanung\Administrator\Helper\SpielplanHelper;

/**
 * Methods supporting a list of Spielplaene records.
 *
 * @since  1.0.6
 */
class SpielplaeneModel extends ListModel
{
    protected $option = 'com_ttc_spielplanung';
	/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array(), ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'hallennr', 'a.hallennr',
				'ort_key', 'a.ort_key',
				'mannschaft', 'a.mannschaft',
				'datum', 'a.datum',
				'uhrzeit', 'a.uhrzeit',
				'heimmannschaft', 'a.heimmannschaft',
				'h_nummer', 'a.h_nummer',
				'auswaertsmannschaft', 'a.auswaertsmannschaft',
				'a_nummer', 'a.a_nummer',
				'ort', 'a.ort',
			);
		}

		parent::__construct($config, $factory);
	}


	

	

	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{

		$context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $context);

		// Split context into component and optional section
		if (!empty($context))
		{
			$parts = FieldsHelper::extract($context);

			if ($parts)
			{
				$this->setState('filter.component', $parts[0]);
				$this->setState('filter.section', $parts[1]);
			}
		}
		
        // Filter "mannschaft" einlesen
        $mannschaft = $this->getUserStateFromRequest($this->context.'.filter.mannschaft', 'filter_mannschaft'); //$app->getInput()->get('filter.mannschaft', '', 'string');
        $this->setState('filter.mannschaft', $mannschaft);
        // Filter "offeneSpiele" einlesen, um zu entscheiden, was angezeigt wird
        $offenespiele = $this->getUserStateFromRequest($this->context.'.filter.offenespiele', 'filter_offenespiele'); //$app->getInput()->get('filter.offenespiele', '', 'string');
        $this->setState('filter.offenespiele', $offenespiele);


		// List state information.
		parent::populateState('id', 'ASC');

	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string A store id.
	 *
	 * @since   1.0.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.mannschaft');
        $id .= ':' . $this->getState('filter.offenespiele');

		
		return parent::getStoreId($id);
		
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   1.0.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__ttc_spielplan` AS a');
		
		

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.heimmannschaft LIKE ' . $search . '  OR  a.auswaertsmannschaft LIKE ' . $search . ' OR  a.ort LIKE ' . $search . ' )');
			}
		}

        // Filter on mannschaft
		$mannschaft = $this->getState('filter.mannschaft');
        if (!empty($mannschaft)) {
            $query->where($db->quoteName('a.mannschaft') . ' = ' . $db->quote($mannschaft));
        }
		// Filter offene Spiele
		$offenespiele = $this->getState('filter.offenespiele');
        if (!empty($offenespiele)) {
            $query->where($db->quoteName('a.datum') . ' >= CURRENT_DATE');
        }
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol && $orderDirn)
		{
			$query->order((in_array($orderCol, $this->filter_fields, true) ? $orderCol : 'a.id') . ' ' . (strtoupper($orderDirn) === 'DESC' ? 'DESC' : 'ASC'));
		}

		return $query;
	}

	/**
	 * Validiert die CSV vollstaendig und schreibt sie transaktional direkt in #__ttc_spielplan.
     *
     * Erwartete CSV-Spalten (Header-Zeile erforderlich):
	 *   Termin, HeimVereinName, HeimMannschaftNr, GastVereinName,
	 *   GastMannschaftNr, HalleName, HalleStrasse, HallePLZ, HalleOrt
	 *
	 * @param   string  $tmpFile  Pfad zur hochgeladenen temporären Datei
	 *
	 * @return  int  Anzahl importierter Datensätze
	 *
	 * @throws  \RuntimeException
	 *
	 * @since   1.0.6
	 */
public function importSpielplan(string $tmpFile): int
    {
        $this->assertCan('core.create');
        $records = (new \Ttc\Component\Spielplanung\Administrator\Service\SpielplanCsvReader())->read($tmpFile);
        $db = $this->getDatabase();
        $db->transactionStart();
        try {
            foreach ($records as $record) {
                $db->insertObject('#__ttc_spielplan', $record);
            }
            $db->transactionCommit();
        } catch (\Throwable $e) {
            $db->transactionRollback();
            throw $e;
        }
        $this->cleanCache();
        return count($records);
    }

    private function assertCan(string $action): void
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('core.manage', 'com_ttc_spielplanung') || !$user->authorise($action, 'com_ttc_spielplanung')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

/**
	 * Löscht alle Spielplan-Einträge, bei denen mannschaft > 0 ist.
	 *
	 * @return  int  Anzahl der gelöschten Datensätze
	 *
	 * @throws  \RuntimeException
	 *
	 * @since   1.0.6
	 */
	public function deleteSpielplan(): int
	{
        $this->assertCan('core.delete');
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ttc_spielplan'))
		      ->where($db->quoteName('mannschaft') . ' > 0')
			  ->where($db->quoteName('datum') . ' > current_timestamp');

		$db->setQuery($query);
		$db->execute();

		$count = (int) $db->getAffectedRows();
        $this->cleanCache();
        return $count;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
		

		return $items;
	}
}
