<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Halleninfos
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Hallenschliesszeiten\Administrator\View\Halleninfos;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Ttc\Component\Hallenschliesszeiten\Administrator\Helper\HalleninfosHelper;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
/**
 * View class for a list of Halleninfos.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	public $filterForm;
    public $activeFilters;
    public $sidebar;
    protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->getModel()->getState();
		$this->items = $this->getModel()->getItems();
		$this->pagination = $this->getModel()->getPagination();
		$this->filterForm = $this->getModel()->getFilterForm();
		$this->activeFilters = $this->getModel()->getActiveFilters();

		// Check for errors.
		if (count($errors = $this->getModel()->getErrors()))
		{
			throw new \Exception(implode("\n", $errors));
		}

		Sidebar::addEntry(Text::_('COM_HALLENSCHLIESSZEITEN_TITLE_SCHLIESSZEITEN'), 'index.php?option=com_hallenschliesszeiten&view=schliesszeiten', 'Halleninfos' === 'Schliesszeiten');
        Sidebar::addEntry(Text::_('COM_HALLENINFOS_TITLE_HALLENINFOS'), 'index.php?option=com_hallenschliesszeiten&view=halleninfos', 'Halleninfos' === 'Halleninfos');
        $this->addToolbar();

		$this->sidebar = Sidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$state = $this->getModel()->getState();
		$canDo = HalleninfosHelper::getActions();

		ToolbarHelper::title(Text::_('COM_HALLENINFOS_TITLE_HALLENINFOS'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Halleninfos';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				$toolbar->addNew('halleninfo.add');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if (isset($this->items[0]->state))
			{
				$childBar->publish('halleninfos.publish')->listCheck(true);
				$childBar->unpublish('halleninfos.unpublish')->listCheck(true);
				$childBar->archive('halleninfos.archive')->listCheck(true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete('halleninfos.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
			}

			$childBar->standardButton('duplicate')
				->text('JTOOLBAR_DUPLICATE')
				->icon('fas fa-copy')
				->task('halleninfos.duplicate')
				->listCheck(true);

			if (isset($this->items[0]->checked_out))
			{
				$childBar->checkin('halleninfos.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state))
			{
				$childBar->trash('halleninfos.trash')->listCheck(true);
			}
		}

		

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{

			if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
			{
				$toolbar->delete('halleninfos.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		if ($canDo->get('core.admin'))
		{
			$toolbar->preferences('com_hallenschliesszeiten');
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_hallenschliesszeiten&view=halleninfos');
	}
	
	/**
	 * Method to order fields 
	 *
	 * @return void 
	 */
	protected function getSortFields()
	{
		return array(
			'a.`id`' => Text::_('JGRID_HEADING_ID'),
			'a.`ordering`' => Text::_('JGRID_HEADING_ORDERING'),
			'a.`datum`' => Text::_('COM_HALLENINFOS_HALLENINFOS_DATUM'),
			'a.`information`' => Text::_('COM_HALLENINFOS_HALLENINFOS_INFORMATION'),
		);
	}

	/**
	 * Check if state is set
	 *
	 * @param   mixed  $state  State
	 *
	 * @return bool
	 */
	public function getState($state)
	{
		return isset($this->state->{$state}) ? $this->state->{$state} : false;
	}
}
