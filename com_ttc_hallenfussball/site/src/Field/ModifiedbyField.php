<?php
/**
 * @version    CVS: 2.1
 * @package    Com_Ttc_hallenfussball
 * @author     Thorsten Austen <fb@ttc-nordend.de>
 * @copyright  Copyright (C) 2013-2019. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttchallenfussball\Component\Ttc_hallenfussball\Site\Field;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\FormField;

/**
 * Supports an HTML select list of categories
 *
 * @since  2.1
 */
class ModifiedbyField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.1
	 */
	protected $type = 'modifiedby';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   2.1
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html   = array();
		$user   = Factory::getApplication()->getIdentity();
		$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $user->id . '" />';

		if (!$this->hidden)
		{
			$html[] = "<div>" . $user->name . " (" . $user->username . ")</div>";
		}

		return implode($html);
	}
}
