<?php
/**
 * @version    CVS: 1.0.6
 * @package    Com_Spielplan
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Spielplanung\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\Event\Model;
use Joomla\CMS\Event\AbstractEvent;


/**
 * Spielplan model.
 *
 * @since  1.0.6
 */
class SpielplanModel extends AdminModel
{
    protected $option = 'com_ttc_spielplanung';
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.0.6
	 */
	protected $text_prefix = 'COM_TTC_SPIELPLANUNG_SPIELPLAN';

	/**
	 * @var    string  Alias to manage history control
	 *
	 * @since  1.0.6
	 */
	public $typeAlias = 'com_ttc_spielplanung.spielplan';

	/**
	 * @var    null  Item data
	 *
	 * @since  1.0.6
	 */
	protected $item = null;

	
	

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table    A database object
	 *
	 * @since   1.0.6
	 */
	public function getTable($type = 'Spielplan', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 *
	 * @since   1.0.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm(
								'com_ttc_spielplanung.spielplan', 
								'spielplan',
								array(
									'control' => 'jform',
									'load_data' => $loadData 
								)
							);

		
			if($form && $form->getFieldAttribute('uhrzeit', 'default') == 'NOW'){
				$form->setFieldAttribute('uhrzeit', 'default', date('H:i'));
			}

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.0.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_ttc_spielplanung.edit.spielplan.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;
			
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.0.6
	 */
	public function getItem($pk = null)
	{
		
			if ($item = parent::getItem($pk))
			{
				if (isset($item->params))
				{
					$item->params = json_encode($item->params);
				}
				
					if($item->uhrzeit) {
					$item->uhrzeit = Factory::getDate($item->uhrzeit)->format(Text::_('H:i'));
					}
				// Do any procesing on fields here if needed
			}

			return $item;
		
	}

	/**
	 * Method to duplicate an Spielplan
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('core.manage', 'com_ttc_spielplanung') || !$user->authorise('core.create', 'com_ttc_spielplanung')) {
            throw new \RuntimeException(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'), 403);
        }
        if (!$pks) {
            throw new \InvalidArgumentException(Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_NO_ELEMENT_SELECTED'));
        }
        foreach ($pks as $pk) {
            if (filter_var($pk, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                throw new \InvalidArgumentException(Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_NO_ELEMENT_SELECTED'));
            }
        }
        $db = $this->getDatabase();
        $db->transactionStart();
        try {
            foreach ($pks as $pk) {
                $item = $this->getItem((int) $pk);
                if (!$item || empty($item->id)) {
                    throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_NO_ELEMENT_SELECTED'));
                }
                $data = (array) $item;
                $data['id'] = 0;
                unset($data['asset_id'], $data['checked_out'], $data['checked_out_time']);
                // Let AdminModel handle validation, typed Joomla events and plugin vetoes.
                if (!$this->save($data)) {
                    throw new \RuntimeException((string) $this->getError());
                }
            }
            $db->transactionCommit();
        } catch (\Throwable $e) {
            $db->transactionRollback();
            throw $e;
        }
        $this->cleanCache();
        return true;
    }

/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   Table  $table  Table Object
	 *
	 * @return  void
	 *
	 * @since   1.0.6
	 */
	protected function prepareTable($table)
	{
		

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (isset($table->ordering) && $table->ordering === '')
			{
				$db = $this->getDatabase();
				$db->setQuery('SELECT MAX(ordering) FROM #__ttc_spielplan');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}
}
