<?php
/**
 * @copyright	Copyright (c) 2015 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$datum_alt = '0001-01-01';
foreach ($daten as $row) {
    $datum_umw = substr($row['datum'], 8, 2).".".substr($row['datum'], 5, 2).".".substr($row['datum'], 0, 4);
    if ($datum_alt != $datum_umw) {
		echo "<span style='text-decoration: underline;'>".$row['wochentag'].$datum_umw."</span><br>";
	}
    $datum_alt = $datum_umw;    
    $uhrzeit = substr($row['uhrzeit'],0,5)." Uhr";
    $heim = $row['heimmannschaft']." ".$row['h_nummer'];
    $auswaerts = $row['auswaertsmannschaft']." ".$row['a_nummer'];
    echo $uhrzeit." ".$heim." - ".$auswaerts."<br>";
    echo "<em>".$row['ort']."</em><br>";
}
?>
