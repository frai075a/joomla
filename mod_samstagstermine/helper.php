<?php
/**
 * @copyright	Copyright (c) 2024 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;

/**
 * Austen - Samstagstermine Helper Class.
 *
 * @package		Joomla.Site
 * @subpakage	Austen.Samstagstermine
 */
class modSamstagstermineHelper {

		
	public static function getSamstagstermine($params)
	{	

        // Obtain a database connection
	//$db = JFactory::getDbo();
	$db = Factory::getContainer()->get('DatabaseDriver');	
	// Retrieve the shout
    $q2  = $db->getQuery(true) 				
				->select('startdate as datum, schliessinfo as heimmannschaft')
                ->from('TTC_view_ferien');
	$q1 = $db->getQuery(true)
                ->select(array('datum', 'heimmannschaft'))
//				->select('datum, uhrzeit, heimmannschaft, mannschaft')
	            ->from($db->quoteName('#__ttc_spielplan'))
	            ->union($q2)
				->where('heimmannschaft = "TTC Nordend Frankfurt" AND datum >= CURDATE() and WEEKDAY(datum) = 5')
				->order($db->quoteName(array('datum')));
	// Prepare the query
	$db->setQuery($q1);
	// Load the rows.
    $result = $db->loadAssocList();
//     $result = $db->loadObjectList();
	return $result;
    } 	


}
?>