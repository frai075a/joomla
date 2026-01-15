<?php
/**
 * @version    CVS: 2.1.0
 * @package    Com_Qttr_rechner
 * @author     Doganer/Austen <fb@ttc-nordend.de>
 * @copyright  2022 Thorsten
 * @license    GNU General Public License Version 2 oder später; siehe LICENSE.txt
 */

namespace Qttr\Component\Qttr_rechner\Site\Field;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\FormField;

/**
 * Supports an HTML select list of categories
 *
 * @since  2.1.0
 */
class CreatedbyField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since  2.1.0
	 */
	protected $type = 'createdby';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   2.1.0
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();

		// Load user
		$user_id = $this->value;

		if ($user_id)
		{
			$user = Factory::getUser($user_id);
		}
		else
		{
			$user   = Factory::getUser();
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $user->id . '" />';
		}

		if (!$this->hidden)
		{
			$html[] = "<div>" . $user->name . " (" . $user->username . ")</div>";
		}

		return implode($html);
	}
}
