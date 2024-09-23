<?php
/**
 * @copyright	Copyright (c) 2015 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Austen - OwnQttr Helper Class.
 *
 * @package		Joomla.Site
 * @subpakage	Austen.OwnQttr
 */
class modOwnQttrHelper {

// Create an object for the record we are going to update.

	public static function update_own_qttr($qttr, $mein_alter, $beginner, $pause, $num_rows)
	{
//Get user data
        $user = JFactory::getUser();
        $object = new stdClass();
 
		// Must be a valid primary key value.
		$object->user_id = $user->id;
        $object->qttr_wert = $qttr;
        $object->mein_alter = $mein_alter;
        $object->beginner = $beginner;
        $object->pause = $pause;
 //$object->qttr_wert = 2000;
		// Update their details in the users table using id as the primary key.
        if ($num_rows == 1)
            $result = JFactory::getDbo()->updateObject('#__qttr_eigener', $object, 'user_id');     
        else
            $result = JFactory::getDbo()->insertObject('#__qttr_eigener', $object);


	}
		
	public static function getOwnQTTR($params, &$num_rows)
	{	
		//Get user data
        $user = JFactory::getUser();
        // Obtain a database connection
		$db = JFactory::getDbo();
        
		// Retrieve the shout
		$query = $db->getQuery(true)
		            ->select(array('qttr_wert', 'mein_alter', 'beginner', 'pause', 'COUNT(*)'))
		            ->from($db->quoteName('#__qttr_eigener'))
		            ->where('user_id = '.$user->id)
                    ->group($db->quoteName(array('qttr_wert', 'mein_alter', 'beginner', 'pause')));
		// Prepare the query
		$db->setQuery($query);
		// Load the row.
		//$num_rows = $db->getNumRows();
        $result = $db->loadRow();
		//$result[5] = $num_rows; 
		return $result;
        	
	}
}