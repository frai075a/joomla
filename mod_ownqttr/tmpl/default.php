<?php
/**
 * @copyright	Copyright (c) 2015 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
if (!isset($daten['0'])) 
	echo '<b>Achtung!<br><br>Es liegen noch keine eigenen QTTR Daten vor. Bitte erst eigene QTTR (s.u.) erfassen und den Button Update dr&uuml;cken:</b><br><br>';
else
	echo '<i>Eigene QTTR k&ouml;nnen angepasst werden, hierzu u.g. Werte &auml;ndern und danach den Button Update dr&uuml;cken:</b><br><br>';
?>
<form id="update-my-qttr-<?php echo $user_id; ?>" style="display:inline" action="<?php echo Route::_('index.php?option=com_qttr_rechner&view=spiele'); ?>" method="post" class="form-validate" enctype="multipart/form-data">
	<label for="myqttr">Eigener QTTR-Wert:</label> 
    <input type="text" id="myqttr" name="myqttr" <?php if (isset($daten['0'])) echo "value='".$daten[0]."'>"; else echo ">"; ?>
    <label for="mein_alter">Eigenes Alter:</label> 
    <select name="alter"> 
        <option <?php if (isset($daten['1']) && $daten['1'] == 1) echo "selected "; ?>value = "1">> 20 Jahre</option>
        <option <?php if (isset($daten['1']) && $daten['1'] == 2) echo "selected "; ?>value = "2">16 - 20 Jahre</option>
        <option <?php if (isset($daten['1']) && $daten['1'] == 3) echo "selected "; ?>value = "3">< 16 Jahre</option>
    </select>
    <label for="beginner">Weniger als 30 Spiele gewertet ?</label> 
    <select name="beginner"> 
        <option <?php if (isset($daten['2']) && $daten['2'] == 'n') echo "selected "; ?>value = "n">Nein</option>
        <option <?php if (isset($daten['2']) && $daten['2'] == 'j') echo "selected "; ?>value = "j">Ja</option>
    </select>
    <label for="pause">Im kompletten letzten Jahr pausiert ?</label> 
    <select name="pause"> 
        <option <?php if (isset($daten['3']) && $daten['3'] == 'n') echo "selected "; ?> value = "n">Nein</option>
        <option <?php if (isset($daten['3']) && $daten['3'] == 'j') echo "selected "; ?>value = "j">Ja</option>
    </select>
    <br><br>
    <input type="hidden" name="task" value="update" />
    <input type="hidden" name="num_rows" value="<?php echo $daten['4']; ?>" />
    <input type="submit" class="btn btn-danger"value="Update">
</form>

