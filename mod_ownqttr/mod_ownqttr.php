<?php
/**
 * @copyright	@copyright	Copyright (c) 2015 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';
use Joomla\CMS\Helper\ModuleHelper;
$class_sfx = htmlspecialchars($params->get('class_sfx'));
//$myqttr = $params->get('myqttr', '0');
//$daten = modOwnQttrHelper::getOwnQTTR($myqttr);
if ( isset($_POST['task']) AND $_POST['task'] == 'update')
    $ergebnis=modOwnQttrHelper::update_own_qttr($_POST['myqttr'], $_POST['alter'], $_POST['beginner'], $_POST['pause'], $_POST['num_rows'] );

$daten = modOwnQttrHelper::getOwnQTTR($params, $num_rows);
require ModuleHelper::getLayoutPath('mod_ownqttr', $params->get('layout', 'default'));