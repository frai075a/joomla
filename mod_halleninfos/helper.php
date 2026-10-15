<?php
/**
 * @copyright	Copyright (c) 2024 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;

/**
 * Austen - Halleninfos Helper Class.
 *
 * @package		Joomla.Site
 * @subpakage	Austen.Halleninfos
 */
class modHalleninfosHelper {

		
	public static function getHalleninfos($params)
	{	

        // Obtain a database connection
	//$db = JFactory::getDbo();
	$db = Factory::getContainer()->get('DatabaseDriver');
	// Retrieve the shout
 	$q1 = $db->getQuery(true)
                ->select(array('datum', 'information', 'id'))
	            ->from($db->quoteName('#__ttc_halleninfos'))
				->where('datum >= CURDATE()')
				->order($db->quoteName(array('datum')));
	// Prepare the query
	$db->setQuery($q1);
	// Load the rows.
    $result = $db->loadAssocList();
	return $result;
    } 	


}
?>