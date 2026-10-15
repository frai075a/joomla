<?php
/**
 * @version    CVS: 2.1
 * @package    Com_Ttc_hallenbuchung
 * @author     Thorsten Austen <fb@ttc-nordend.de>
 * @copyright  Copyright (C) 2013-2019. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Event\Model\NormaliseRequestDataEvent;

/**
 * Hallenfussballteilnahme class.
 *
 * @since  2.1
 */
class HallenfussballteilnahmeformController extends FormController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return  void
	 *
	 * @since   2.1
	 *
	 * @throws  Exception
	 */
	public function edit($key = NULL, $urlVar = NULL)
	{
		// Set the user id for the user to edit in the session.
		$editId = $this->input->getInt('id', 0);
		$this->app->setUserState('com_ttc_hallenbuchung.edit.hallenfussballteilnahme.id', $editId);

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_ttc_hallenbuchung&view=hallenfussballteilnahmeform&layout=edit', false));
	}

	/**
	 * Method to save data.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   2.1
	 */
	public function save($key = NULL, $urlVar = NULL)
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$model = $this->getModel('Hallenfussballteilnahmeform', 'Site');

		// Get the user data.
		$data = $this->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new \Exception($model->getError(), 500);
		}

		// Send an object which can be modified through the plugin event
		$objData = (object) $data;
		$event = $this->app->getDispatcher()->dispatch(
			'onContentNormaliseRequestData',
			new NormaliseRequestDataEvent('onContentNormaliseRequestData', [
				'context' => $this->option . '.' . $this->context,
				'data' => $objData,
				'subject' => $form,
			])
		);

		$data = (array) $event->getData();

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$this->app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$jform = $this->input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$this->app->setUserState('com_ttc_hallenbuchung.edit.hallenfussballteilnahme.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $this->app->getUserState('com_ttc_hallenbuchung.edit.hallenfussballteilnahme.id');
			$this->setRedirect(Route::_('index.php?option=com_ttc_hallenbuchung&view=hallenfussballteilnahmeform&layout=edit&id=' . $id, false));

			$this->redirect();
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$this->app->setUserState('com_ttc_hallenbuchung.edit.hallenfussballteilnahme.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $this->app->getUserState('com_ttc_hallenbuchung.edit.hallenfussballteilnahme.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_ttc_hallenbuchung&view=hallenfussballteilnahmeform&layout=edit&id=' . $id, false));
			$this->redirect();
		}

		// Clear the profile id from the session.
		$this->app->setUserState('com_ttc_hallenbuchung.edit.hallenfussballteilnahme.id', null);

		// Redirect to the list screen.
		if (!empty($return))
		{
			$this->setMessage(Text::_('COM_TTC_HALLENBUCHUNG_ITEM_SAVED_SUCCESSFULLY'));
		}
		
		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_ttc_hallenbuchung&view=hallenfussballteilnehmer' : $item->link);
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$this->app->setUserState('com_ttc_hallenbuchung.edit.hallenfussballteilnahme.data', null);

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $data);

	}

	/**
	 * Method to abort current operation
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function cancel($key = NULL)
	{
		// Clear the profile id from the session.
		$this->app->setUserState('com_ttc_hallenbuchung.edit.hallenfussballteilnahme.id', null);

		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_ttc_hallenbuchung&view=hallenfussballteilnehmer' : $item->link);
		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Method to remove data
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.1
	 */
	public function remove()
	{
		$model = $this->getModel('Hallenfussballteilnahmeform', 'Site');
		$pk    = $this->input->getInt('id');

		$menu = $this->app->getMenu();
		$item = $menu->getActive();
		$url = (empty($item->link) ? 'index.php?option=com_ttc_hallenbuchung&view=hallenfussballteilnehmer' : $item->link);

		// Attempt to delete the data
		try
		{
			// Clear id from the session.
			$this->app->setUserState('com_ttc_hallenbuchung.edit.hallenfussballteilnahme.id', null);

			if ($model->delete($pk))
			{
				$this->setMessage(Text::_('COM_TTC_HALLENBUCHUNG_ITEM_DELETED_SUCCESSFULLY'));
			}
			else
			{
				$this->setMessage(Text::_('COM_TTC_HALLENBUCHUNG_ITEM_DELETED_UNSUCCESSFULLY'), 'warning');
			}

			$this->setRedirect(Route::_($url, false));
			// Flush the data from the session.
			$this->app->setUserState('com_ttc_hallenbuchung.edit.hallenfussballteilnahme.data', null);
		}
		catch (\Exception $e)
		{
			$errorType = ($e->getCode() == '404') ? 'error' : 'warning';
			$this->setMessage($e->getMessage(), $errorType);
			$this->setRedirect(Route::_($url, false));
		}
	}

	/**
     * Function that allows child controller access to model data
     * after the data has been saved.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
    {
    }
}
