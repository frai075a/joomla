<?php
/**
 * @copyright	Copyright (c) 2015 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
/**
 * Austen - Spielplan Helper Class.
 *
 * @package		Joomla.Site
 * @subpakage	Austen.Spielplan
 */
class modSpielplanHelper {

		
	public static function getSpielplan($params)
	{	

        // Obtain a database connection
	//$db = JFactory::getDbo();
	$db = Factory::getContainer()->get('DatabaseDriver');
    // $inliste="('2015-09-18')";
    $inliste=modSpielplanHelper::buildINString($params); 
	// Retrieve the shout
	$query = $db->getQuery(true)
                ->select(array('datum', 'uhrzeit', 'heimmannschaft', 'h_nummer', 'auswaertsmannschaft', 'a_nummer', 'ort',
                             'CASE WHEN WEEKDAY(datum) = 0 THEN "Mo., "
                                  WHEN WEEKDAY(datum) = 1 THEN "Di., "
                       	          WHEN WEEKDAY(datum) = 2 THEN "Mi., "
                       	          WHEN WEEKDAY(datum) = 3 THEN "Do., "
                       	          WHEN WEEKDAY(datum) = 4 THEN "Fr., "
                       	          WHEN WEEKDAY(datum) = 5 THEN "Sa., "
                       	          WHEN WEEKDAY(datum) = 6 THEN "So., "					 
                       	          ELSE ""
                             END AS wochentag'))
	            ->from($db->quoteName('#__ttc_spielplan'))
	            ->where('datum between CURDATE() AND DATE_ADD(CURDATE(), interval 13 day) OR mannschaft = "0" AND datum >= CURDATE() OR datum IN '.$inliste.' AND datum >= CURDATE()')
                ->order($db->quoteName(array('datum', 'uhrzeit', 'heimmannschaft')));
	// Prepare the query
	$db->setQuery($query);
	// Load the rows.
    $result = $db->loadAssocList();

	return $result;
    } 	

    public static function buildINString($params)
    {
	$db1 = JFactory::getDbo();
        
	// Retrieve the shout
	$query1 = $db1->getQuery(true)
                ->select(array('mannschaft', 'min(datum) as min_datum'))
	            ->from($db1->quoteName('#__ttc_spielplan'))
	            ->where('datum > CURDATE()')
                ->group($db1->quoteName(array('mannschaft')));
	// Prepare the query
	$db1->setQuery($query1);
	// Load the rows.
    $termine = $db1->loadAssocList();
 
    $inliste = "(\"";
    foreach ($termine as $row) {
        $inliste = $inliste.$row['min_datum']."\", \"";
    }
    if (strlen($inliste) > 2) {
        $inliste=substr($inliste, 0, -3).")";
    }
    else
    {
        //es konnten keine zukünftigen Spiele gefunden werden, der IN-String bekommt ein Dummy-Datum verpasst
        $inliste="('2015-01-01')";
    }
   //  echo "Inliste : ".$inliste;
    return $inliste;
    }

}