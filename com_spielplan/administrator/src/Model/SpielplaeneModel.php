<?php
/**
 * @version    CVS: 1.0.6
 * @package    Com_Spielplan
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Spielplan\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use Ttc\Component\Spielplan\Administrator\Helper\SpielplanHelper;

/**
 * Methods supporting a list of Spielplaene records.
 *
 * @since  1.0.6
 */
class SpielplaeneModel extends ListModel
{
	/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
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

		parent::__construct($config);
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
        $mannschaft = $this->getUserStateFromRequest($this->mannschaft.'.filter.mannschaft', 'filter_mannschaft'); //$app->getInput()->get('filter.mannschaft', '', 'string');
        $this->setState('filter.mannschaft', $mannschaft);
        // Filter "offeneSpiele" einlesen, um zu entscheiden, was angezeigt wird
        $offenespiele = $this->getUserStateFromRequest($this->offenespiele.'.filter.offenespiele', 'filter_offenespiele'); //$app->getInput()->get('filter.offenespiele', '', 'string');
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
		$id .= ':' . $this->getState('filter.state');

		
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
		$db    = $this->getDbo();
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
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Importiert eine CSV-Datei in die temporäre Tabelle und überträgt
	 * die Daten per INSERT ... SELECT in #__ttc_spielplan.
	 * Läuft vollständig in einer Transaktion (Rollback bei Fehler).
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
		$db = $this->getDbo();

		// ----------------------------------------------------------------
		// 1. CSV einlesen
		// ----------------------------------------------------------------

		// Datei öffnen – sofort auf Fehler prüfen
		$handle = fopen($tmpFile, 'r');

		if ($handle === false)
		{
			throw new \RuntimeException('COM_SPIELPLAN_IMPORT_ERROR_FILE_READ');
		}

		// Encoding der Datei erkennen (4 KB Sample reicht für mb_detect_encoding)
		$sample = fread($handle, 4096);
		rewind($handle);

		$encoding = mb_detect_encoding(
			$sample,
			['UTF-8', 'Windows-1252', 'ISO-8859-1'],
			true
		);

		// Fallback falls nichts erkannt wird
		if ($encoding === false)
		{
			$encoding = 'Windows-1252';
		}

		$convertToUtf8 = function ($value) use ($encoding) {
			if ($value === null || $value === '')
			{
				return '';
			}

			return trim(mb_convert_encoding($value, 'UTF-8', $encoding));
		};

		// BOM entfernen falls vorhanden (UTF-8 BOM: EF BB BF)
		$firstBytes = fread($handle, 3);

		if ($firstBytes !== "\xEF\xBB\xBF")
		{
			rewind($handle);
		}

		// Header-Zeile einlesen
		$rawHeaders = fgetcsv($handle, 0, ';');

		if ($rawHeaders === false || empty($rawHeaders))
		{
			fclose($handle);
			throw new \RuntimeException('COM_SPIELPLAN_IMPORT_ERROR_EMPTY_FILE');
		}

		// Encoding konvertieren und Whitespace bereinigen
		$headers = array_map($convertToUtf8, $rawHeaders);

		// Pflichtfelder prüfen
		$required = [
			'Termin', 'HeimVereinName', 'HeimMannschaftNr',
			'GastVereinName', 'GastMannschaftNr',
			'HalleName', 'HalleStrasse', 'HallePLZ', 'HalleOrt',
		];

		$missing = array_diff($required, $headers);

		if (!empty($missing))
		{
			fclose($handle);
			throw new \RuntimeException(
				'COM_SPIELPLAN_IMPORT_ERROR_MISSING_COLUMNS: ' . implode(', ', $missing)
			);
		}

		$colIndex = array_flip($headers);

		// Alle Datenzeilen sammeln
		$csvRows = [];

		while (($row = fgetcsv($handle, 0, ';')) !== false)
		{
			if (count(array_filter($row)) === 0)
			{
				// Leerzeile überspringen
				continue;
			}
			$row = array_map($convertToUtf8, $row);
		//	$row = array_map('trim', $row);
			$csvRows[] = $row;
		}

		fclose($handle);

		if (empty($csvRows))
		{
			throw new \RuntimeException('COM_SPIELPLAN_IMPORT_ERROR_NO_DATA');
		}

		// ----------------------------------------------------------------
		// 2. Transaktion starten
		// ----------------------------------------------------------------
		$db->transactionStart();

		try
		{
			// Temporäre Tabelle leeren (oder anlegen falls nicht vorhanden)
			$db->setQuery('
				CREATE TABLE IF NOT EXISTS `ttc_spielplan_import_tmp` (
					`Termin`           VARCHAR(20)  NOT NULL DEFAULT \'\',
					`HeimVereinName`   VARCHAR(100) NOT NULL DEFAULT \'\',
					`HeimMannschaftNr` TINYINT(2)   NOT NULL DEFAULT 0,
					`GastVereinName`   VARCHAR(100) NOT NULL DEFAULT \'\',
					`GastMannschaftNr` TINYINT(2)   NOT NULL DEFAULT 0,
					`HalleName`        VARCHAR(100) NOT NULL DEFAULT \'\',
					`HalleStrasse`     VARCHAR(100) NOT NULL DEFAULT \'\',
					`HallePLZ`         VARCHAR(10)  NOT NULL DEFAULT \'\',
					`HalleOrt`         VARCHAR(100) NOT NULL DEFAULT \'\'
				) DEFAULT COLLATE=utf8mb4_unicode_ci
			')->execute();

			$db->setQuery('TRUNCATE TABLE `ttc_spielplan_import_tmp`')->execute();

			// CSV-Zeilen in die temporäre Tabelle einfügen
			foreach ($csvRows as $row)
			{
				$obj                   = new \stdClass();
				$obj->Termin           = $row[$colIndex['Termin']]           ?? '';
				$obj->HeimVereinName   = $row[$colIndex['HeimVereinName']]   ?? '';
				$obj->HeimMannschaftNr = (int) ($row[$colIndex['HeimMannschaftNr']] ?? 0);
				$obj->GastVereinName   = $row[$colIndex['GastVereinName']]   ?? '';
				$obj->GastMannschaftNr = (int) ($row[$colIndex['GastMannschaftNr']] ?? 0);
				$obj->HalleName        = $row[$colIndex['HalleName']]        ?? '';
				$obj->HalleStrasse     = $row[$colIndex['HalleStrasse']]     ?? '';
				$obj->HallePLZ         = $row[$colIndex['HallePLZ']]         ?? '';
				$obj->HalleOrt         = $row[$colIndex['HalleOrt']]         ?? '';

				$db->insertObject('ttc_spielplan_import_tmp', $obj);
			}

			// ----------------------------------------------------------------
			// 3. INSERT ... SELECT in die Zieltabelle
			// ----------------------------------------------------------------
			$db->setQuery('
				INSERT INTO `#__ttc_spielplan`
					(mannschaft, datum, uhrzeit,
					 heimmannschaft, h_nummer,
					 auswaertsmannschaft, a_nummer,
					 ort, hallennr, ort_key)
				SELECT
					CASE
						WHEN `HeimVereinName` = \'TTC Nordend Frankfurt\' THEN `HeimMannschaftNr`
						ELSE `GastMannschaftNr`
					END AS mannschaft,
					STR_TO_DATE(
						CONCAT(SUBSTR(Termin,7,4),\'-\',SUBSTR(Termin,4,2),\'-\',SUBSTR(Termin,1,2)),
						\'%Y-%m-%d\'
					) AS datum,
					TIME(SUBSTR(Termin,12,5)) AS uhrzeit,
					`HeimVereinName` AS heimmannschaft,
					CASE `HeimMannschaftNr`
						WHEN 1  THEN \'I\'    WHEN 2  THEN \'II\'   WHEN 3  THEN \'III\'
						WHEN 4  THEN \'IV\'   WHEN 5  THEN \'V\'    WHEN 6  THEN \'VI\'
						WHEN 7  THEN \'VII\'  WHEN 8  THEN \'VIII\' WHEN 9  THEN \'IX\'
						WHEN 10 THEN \'X\'    WHEN 11 THEN \'XI\'   WHEN 12 THEN \'XII\'
						WHEN 13 THEN \'XIII\'
					END AS h_nummer,
					`GastVereinName` AS auswaertsmannschaft,
					CASE `GastMannschaftNr`
						WHEN 1  THEN \'I\'    WHEN 2  THEN \'II\'   WHEN 3  THEN \'III\'
						WHEN 4  THEN \'IV\'   WHEN 5  THEN \'V\'    WHEN 6  THEN \'VI\'
						WHEN 7  THEN \'VII\'  WHEN 8  THEN \'VIII\' WHEN 9  THEN \'IX\'
						WHEN 10 THEN \'X\'    WHEN 11 THEN \'XI\'   WHEN 12 THEN \'XII\'
						WHEN 13 THEN \'XIII\'
					END AS a_nummer,
					CONCAT(`HalleName`, \', \', `HalleStrasse`, \', \', `HallePLZ`, \' \', `HalleOrt`) AS ort,
					0    AS hallennr,
					0    AS ort_key
				FROM `ttc_spielplan_import_tmp`
				WHERE 1
			')->execute();

			$count = (int) $db->getAffectedRows();

			$db->transactionCommit();
		}
		catch (\Exception $e)
		{
			$db->transactionRollback();
			throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
		}

		return $count;
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
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ttc_spielplan'))
		      ->where($db->quoteName('mannschaft') . ' > 0');

		$db->setQuery($query);
		$db->execute();

		return (int) $db->getAffectedRows();
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
