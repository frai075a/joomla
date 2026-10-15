<?php
/**
 * @copyright	Copyright (c) 2024 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$datum_alt = '0001-01-01';
foreach ($daten as $row) {
    $datum_umw = substr($row['datum'], 8, 2).".".substr($row['datum'], 5, 2).".".substr($row['datum'], 0, 4);
  
    if ($datum_alt != $datum_umw) {
// nur 1 Ereignis pro Termin rausschreiben, weiteres wird überlesen		
		
		if ($row['heimmannschaft'] == 'TTC Nordend') {
			$grund = " TT - Heimspiel";
		}
		else {
			$grund = " ".$row['heimmannschaft'];
		}
		echo "<span style='text-decoration: underline;'>".$datum_umw."</span>".$grund."<br>";
	    $datum_alt = $datum_umw;    
	}
}