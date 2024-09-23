<?php
/**
 * @copyright	@copyright	Copyright (c) 2022 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';
use Joomla\CMS\Helper\ModuleHelper;

$class_sfx = htmlspecialchars($params->get('class_sfx'));

$num_rows=0;
$ownqttrvalues=modQttrcalcHelper::GetOwnQTTRValues($params); 
if (isset($ownqttrvalues))
{
	$aenderungskonstante=modQttrcalcHelper::GetAenderungskonstante($ownqttrvalues[1], $ownqttrvalues[2], $ownqttrvalues[3]); 
	$daten = modQttrcalcHelper::getQttrcalc($params, $num_rows);
}

require ModuleHelper::getLayoutPath('mod_qttrcalc', $params->get('layout', 'default'));