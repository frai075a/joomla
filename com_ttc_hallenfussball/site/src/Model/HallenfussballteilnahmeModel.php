<?php
/**
 * @version    CVS: 2.1
 * @package    Com_Ttc_hallenfussball
 * @author     Thorsten Austen <fb@ttc-nordend.de>
 * @copyright  Copyright (C) 2013-2019. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttchallenfussball\Component\Ttc_hallenfussball\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Object\CMSObject;
use \Joomla\CMS\User\UserFactoryInterface;
use \Ttchallenfussball\Component\Ttc_hallenfussball\Site\Helper\Ttc_hallenfussballHelper;

/**
 * Ttc_hallenfussball model.
 *
 * @since  2.1
 */
class HallenfussballteilnahmeModel extends ItemModel
{
	public $_item;

	

	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   2.1
	 *
	 * @throws Exception
	 */
	protected function populateState()
	{
		$app  = Factory::getApplication('com_ttc_hallenfussball');
		$user = $app->getIdentity();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_ttc_hallenfussball')) && (!$user->authorise('core.edit', 'com_ttc_hallenfussball')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_ttc_hallenfussball.edit.hallenfussballteilnahme.id');
		}
		else
		{
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_ttc_hallenfussball.edit.hallenfussballteilnahme.id', $id);
		}

		$this->setState('hallenfussballteilnahme.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('hallenfussballteilnahme.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer $id The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function getItem($id = null)
	{
		if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id))
			{
				$id = $this->getState('hallenfussballteilnahme.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table && $table->load($id))
			{
				

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if (isset($table->state) && $table->state != $published)
					{
						throw new \Exception(Text::_('COM_TTC_HALLENFUSSBALL_ITEM_NOT_LOADED'), 403);
					}
				}

				// Convert the Table to a clean CMSObject.
				$properties  = $table->getProperties(1);
				$this->_item = ArrayHelper::toObject($properties, CMSObject::class);

				
			}

			if (empty($this->_item))
			{
				throw new \Exception(Text::_('COM_TTC_HALLENFUSSBALL_ITEM_NOT_LOADED'), 404);
			}
		}

		

		 $container = \Joomla\CMS\Factory::getContainer();

		 $userFactory = $container->get(UserFactoryInterface::class);

		if (isset($this->_item->created_by))
		{
			$user = $userFactory->loadUserById($this->_item->created_by);
			$this->_item->created_by_name = $user->name;
		}

		return $this->_item;
	}
	


	/**
	 * Get an instance of Table class
	 *
	 * @param   string $type   Name of the Table class to get an instance of.
	 * @param   string $prefix Prefix for the table class name. Optional.
	 * @param   array  $config Array of configuration values for the Table object. Optional.
	 *
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Hallenfussballteilnahme', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 * @param   string $alias Item alias
	 *
	 * @return  mixed
	 * 
	 * @deprecated  No replacement
	 */
	public function getItemIdByAlias($alias)
	{
		$table      = $this->getTable();
		$properties = $table->getProperties();
		$result     = null;
		$aliasKey   = null;
		if (method_exists($this, 'getAliasFieldNameByView'))
		{
			$aliasKey   = $this->getAliasFieldNameByView('hallenfussballteilnahme');
		}
		

		if (key_exists('alias', $properties))
		{
			$table->load(array('alias' => $alias));
			$result = $table->id;
		}
		elseif (isset($aliasKey) && key_exists($aliasKey, $properties))
		{
			$table->load(array($aliasKey => $alias));
			$result = $table->id;
		}
		
			return $result;
		
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer $id The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since   2.1
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('hallenfussballteilnahme.id');
				
		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					return false;
				}
			}
		}

		return true;
		
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer $id The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since   2.1
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('hallenfussballteilnahme.id');

				
		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getApplication()->getIdentity();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
				
	}

	/**
	 * Publish the element
	 *
	 * @param   int $id    Item id
	 * @param   int $state Publish state
	 *
	 * @return  boolean
	 */
	public function publish($id, $state)
	{
		$table = $this->getTable();
				
		$table->load($id);
		$table->state = $state;

		return $table->store();
				
	}

	/**
	 * Method to delete an item
	 *
	 * @param   int $id Element id
	 *
	 * @return  bool
	 */
	public function delete($id)
	{
		$table = $this->getTable();

		
			return $table->delete($id);
		
	}

	
}
