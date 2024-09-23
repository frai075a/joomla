<?php
/**
 * @version    CVS: 1.0.4
 * @package    Com_Spielplan
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Spielplan\Administrator\View\Spielplaene;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Ttc\Component\Spielplan\Administrator\Helper\SpielplanHelper;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
/**
 * View class for a list of Spielplaene.
 *
 * @since  1.0.4
 */
class HtmlView extends BaseHtmlView
{
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
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

		$this->addToolbar();

		$this->sidebar = Sidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.4
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = SpielplanHelper::getActions();

		ToolbarHelper::title(Text::_('COM_SPIELPLAN_TITLE_SPIELPLAENE'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Spielplaene';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				$toolbar->addNew('spielplan.add');
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
				$childBar->publish('spielplaene.publish')->listCheck(true);
				$childBar->unpublish('spielplaene.unpublish')->listCheck(true);
				$childBar->archive('spielplaene.archive')->listCheck(true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete('spielplaene.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
			}

			$childBar->standardButton('duplicate')
				->text('JTOOLBAR_DUPLICATE')
				->icon('fas fa-copy')
				->task('spielplaene.duplicate')
				->listCheck(true);

			if (isset($this->items[0]->checked_out))
			{
				$childBar->checkin('spielplaene.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state))
			{
				$childBar->trash('spielplaene.trash')->listCheck(true);
			}
		}

		

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{

			if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
			{
				$toolbar->delete('spielplaene.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		if ($canDo->get('core.admin'))
		{
			$toolbar->preferences('com_spielplan');
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_spielplan&view=spielplaene');
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
			'a.`mannschaft`' => Text::_('COM_SPIELPLAN_SPIELPLAENE_MANNSCHAFT'),
			'a.`datum`' => Text::_('COM_SPIELPLAN_SPIELPLAENE_DATUM'),
			'a.`uhrzeit`' => Text::_('COM_SPIELPLAN_SPIELPLAENE_UHRZEIT'),
			'a.`heimmannschaft`' => Text::_('COM_SPIELPLAN_SPIELPLAENE_HEIMMANNSCHAFT'),
			'a.`h_nummer`' => Text::_('COM_SPIELPLAN_SPIELPLAENE_H_NUMMER'),
			'a.`auswaertsmannschaft`' => Text::_('COM_SPIELPLAN_SPIELPLAENE_AUSWAERTSMANNSCHAFT'),
			'a.`a_nummer`' => Text::_('COM_SPIELPLAN_SPIELPLAENE_A_NUMMER'),
			'a.`ort`' => Text::_('COM_SPIELPLAN_SPIELPLAENE_ORT'),
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
