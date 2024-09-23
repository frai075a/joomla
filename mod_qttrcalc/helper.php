<?php
/**
 * @copyright	Copyright (c) 2015 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Austen - QTTR Calc Helper Class.
 *
 * @package		Joomla.Site
 * @subpakage	Austen.Qttrcalc
 */
class modQttrcalcHelper {

		
	public static function getQttrcalc($params)
	{	

  // Obtain a database connection
		$db2 = JFactory::getDbo();
    $user = JFactory::getUser();
		$user_id = $user->id;

    $result=modQttrcalcHelper::GetQTTRGames($params);
    return $result;
  }
    
  public static function GetQTTRGames($params)
		{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$user_id = $user->id;

		// Retrieve the shout
		$query = $db->getQuery(true)
    	          ->select(array('qttr_wert', 'sieg', 'letztesspiel'))
      		      ->from($db->quoteName('#__qttr_rechner'))
	        		  ->where($db->quoteName('modified_by').' = '.$user_id.' OR '.$db->quoteName('created_by').' = '.$user_id);
                
		$db->setQuery($query);
		// Load the rows.
    $qttrgames = $db->loadAssocList();
		return $qttrgames;
    }
	
  public static function GetOwnQTTRValues($params)
		{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$user_id = $user->id;

		// Retrieve the shout
		$query = $db->getQuery(true)
    	          ->select(array('qttr_wert', 'mein_alter', 'beginner', 'pause'))
      		      ->from($db->quoteName('#__qttr_eigener'))
	        		  ->where($db->quoteName('user_id').' = '.$user_id);
                
		$db->setQuery($query);
		// Load the rows.
    $ownqttr = $db->loadAssocList();
    // $num_rows = $db->getNumRows();
    foreach ($ownqttr as $row) {
      return [$row['qttr_wert'], $row['mein_alter'], $row['beginner'], $row['pause']];
    }
		if (isset($num_rows))
			return $num_rows;   
		else
			return;
    }

  public static function GetAenderungskonstante($mein_alter, $beginner, $pause)
		{
			if (!isset($mein_alter))
				$mein_alter=3;
			if (!isset($beginner))
				$beginner = 'n';
			if (!isset($pause))
				$pause = 'n';
				
			switch ($mein_alter)
			{
        case 1:
        // > 20 Jahre
             $aenderungskonstante = 16;
             break;
        case 2:
        // > 16 - 20 Jahre
             $aenderungskonstante = 20;
             break;                        
        case 3:
        // < 16
             $aenderungskonstante = 24;
             break;
        default:
             $aenderungskonstante = 0;
             break;

      }
    	if ($beginner == 'j')
             $aenderungskonstante += 4;
    	if ($pause == 'j')
             $aenderungskonstante += 4;
                        
      return $aenderungskonstante;
    }

}