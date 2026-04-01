<?php
/**
 * @version    CVS: 1.0.6
 * @package    Com_Spielplan
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Spielplan\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * Spielplaene list controller class.
 *
 * @since  1.0.6
 */
class SpielplaeneController extends AdminController
{
	/**
	 * Method to clone existing Spielplaene
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function duplicate()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new \Exception(Text::_('COM_SPIELPLAN_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::_('COM_SPIELPLAN_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_spielplan&view=spielplaene');
	}

	/**
	 * Importiert einen Spielplan aus einer hochgeladenen CSV-Datei.
	 * Die CSV wird in eine temporäre Tabelle geladen, dann per
	 * INSERT ... SELECT in #__ttc_spielplan übertragen (Transaktion).
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.0.6
	 */
	public function importSpielplan()
	{
		// CSRF-Token prüfen
		$this->checkToken();

		$user = Factory::getApplication()->getIdentity();

		if (!$user->authorise('core.create', 'com_spielplan'))
		{
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect('index.php?option=com_spielplan&view=spielplaene');
			return;
		}

		// Datei-Upload prüfen
		$app  = Factory::getApplication();
		$file = $app->input->files->get('spielplan_csv', null, 'raw');

		if (empty($file) || $file['error'] !== UPLOAD_ERR_OK)
		{
			$app->enqueueMessage(Text::_('COM_SPIELPLAN_IMPORT_ERROR_NO_FILE'), 'error');
			$this->setRedirect('index.php?option=com_spielplan&view=spielplaene');
			return;
		}

		// Dateierweiterung prüfen
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		if ($ext !== 'csv')
		{
			$app->enqueueMessage(Text::_('COM_SPIELPLAN_IMPORT_ERROR_WRONG_TYPE'), 'error');
			$this->setRedirect('index.php?option=com_spielplan&view=spielplaene');
			return;
		}

		try
		{
			$model = $this->getModel('Spielplaene', 'Administrator');
			$count = $model->importSpielplan($file['tmp_name']);
			$this->setMessage(Text::sprintf('COM_SPIELPLAN_IMPORT_SUCCESS', $count));
		}
		catch (\Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		$this->setRedirect('index.php?option=com_spielplan&view=spielplaene');
	}

	/**
	 * Löscht alle Spielplan-Einträge, bei denen mannschaft > 0 ist.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.6
	 */
	public function deleteSpielplan()
	{
		// CSRF-Token prüfen
		$this->checkToken();

		// Nur Admins mit delete-Recht dürfen diese Aktion ausführen
		$user = Factory::getApplication()->getIdentity();

		if (!$user->authorise('core.delete', 'com_spielplan'))
		{
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect('index.php?option=com_spielplan&view=spielplaene');
			return;
		}

		try
		{
			$model = $this->getModel('Spielplaene', 'Administrator');
			$count = $model->deleteSpielplan();
			$this->setMessage(Text::sprintf('COM_SPIELPLAN_DELETE_SPIELPLAN_SUCCESS', $count));
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		$this->setRedirect('index.php?option=com_spielplan&view=spielplaene');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since   1.0.6
	 */
	public function getModel($name = 'Spielplan', $prefix = 'Administrator', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   1.0.6
	 *
	 * @throws  Exception
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$pks   = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}
}
