<?php
/**
 * @copyright	Copyright (c) 2024 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
use \Joomla\CMS\Router\Route;
$datum_alt = '0001-01-01';
foreach ($daten as $row) {
    $datum_umw = substr($row['datum'], 8, 2).".".substr($row['datum'], 5, 2).".".substr($row['datum'], 0, 4);
 	echo "<span style='text-decoration: underline;'>".$datum_umw."</span><br>";
	echo $row['information'];
	echo "<a href=".Route::_('index.php?option=com_halleninfos&task=halleninfoform.remove&id=' .$row['id'], false, 2)." class='btn btn-mini delete-button' type='button'><i class='icon-trash' ></i></a><br>";
	$datum_alt = $datum_umw;    
	}