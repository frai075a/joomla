<?php
/**
 * @copyright	Copyright (c) 2015 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!isset($ownqttrvalues[0]))
	echo "<i>Bitte erst eigene QTTR-Daten erfassen</i>";
else
{
	echo "<div class='table-responsive'>";
	echo "<table class='table table-striped'><thead>";
	echo "<th class='center'>Spiel-Id</th>";
	echo "<th class='center'>Siegchance</th>";
	echo "<th class='center'>Punkte&aumlnderung</th>";
	echo "<th class='center'>Eigene TTR Basis</th>";
	echo "<th class='center'>TTR Neu</th></thead>";
	
	
	$spielnummer = 0;
	$punktepuffer = 0;
	$myqttr=$ownqttrvalues[0];
	$rowcount=0;
	$rowhighlighter = 1;
	
	echo "<tbody>";
	foreach ($daten as $myrow) {
		$spielnummer++;
	  $gewinnchance = 0;
	  $punkteaenderung = 0;
	  $basis= 10;
	  $qttrdiff = $myrow['qttr_wert'];
	  $qttrbasis = $myqttr;
	  $qttrdiff = $qttrdiff * -1 + $myqttr;
	  $exponent=$qttrdiff/150;
	  $gewinnchance = 1 - (1 / (1 + pow($basis, $exponent))); 
	 	if ($myrow['sieg'] == 'ja')
	     $punkteaenderung = (1 - $gewinnchance) * $aenderungskonstante;
	 	else
	     $punkteaenderung = $gewinnchance * -1 * $aenderungskonstante;
	     
	 	$gewinnchance    = round($gewinnchance*100, 2);
	 	$punkteaenderung = round($punkteaenderung, 0);
	 	$punktepuffer    += $punkteaenderung;
	 	if ($myrow['letztesspiel'] == 'ja')
	 	{
	  	$myqttr = $myqttr + $punktepuffer;
	    $punktepuffer = 0;
	 	}
	 	echo "<tr class='row".$rowcount."'>";
	 	echo "<td>".$spielnummer."</td>";
		echo "<td>".$gewinnchance."%</td>";
		echo "<td>".$punkteaenderung."</td>";
		echo "<td>".$qttrbasis."</td>";
	 	echo "<td>".$myqttr."</td>";
	 	echo "</tr>";
	 	$rowcount+= $rowhighlighter;
	 	$rowhighlighter = $rowhighlighter * -1;
	}
	if ($punktepuffer != 0)
	{
	  $myqttr += $punktepuffer;
	}
	echo "<tfoot><tr>";
	echo "<td class='highlight'></td>";
	echo "<td class='highlight'></td>";
	echo "<td class='highlight'></td>";                
	echo "<td class='highlight'>TTR-neu:</td>";
	echo "<td class='highlight'>".$myqttr."</td>";
	echo "</tr></tfoot>";
	
	echo "</table></div>";   
}
?>
