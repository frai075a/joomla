<?php
/**
 * @copyright	@copyright	Copyright (c) 2015 Austen. All rights reserved.
 * modified in Oct. 2024 for Joomla 5 compatibility
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/helper.php';
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
$daten = modSpielplanHelper::getSpielplan($params);
require ModuleHelper::getLayoutPath('mod_spielplan', $params->get('layout', 'default'));