<?php
/**
 * @copyright	@copyright	Copyright (c) 2024 Austen. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
require_once dirname(__FILE__) . '/helper.php';
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
$daten = modSamstagstermineHelper::getSamstagstermine($params);
require ModuleHelper::getLayoutPath('mod_samstagstermine', $params->get('layout', 'default'));
