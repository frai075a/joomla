<?php
/**
 * @version    CVS: 1.3.0
 * @package    Com_Hallenschliesszeiten
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Hallenschliesszeiten\Site\Helper;

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Class HallenschliesszeitenFrontendHelper
 *
 * @since  1.3.0
 */
class HallenschliesszeitenHelper
{
	

	/**
	 * Gets the files attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
		$query = $db->createQuery();

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

	/**
	 * Gets the edit permission for an user
	 *
	 * @param   mixed  $item  The item
	 *
	 * @return  bool
	 */
	public static function canUserEdit($item)
	{
		$permission = false;
		$user       = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.edit', 'com_hallenschliesszeiten') || (isset($item->created_by) && $user->authorise('core.edit.own', 'com_hallenschliesszeiten') && $item->created_by == $user->id) || $user->authorise('core.create', 'com_hallenschliesszeiten'))
		{
			$permission = true;
		}

		return $permission;
	}
}
