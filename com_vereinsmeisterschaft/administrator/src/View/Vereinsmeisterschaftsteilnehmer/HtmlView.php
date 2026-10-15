<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Vereinsmeisterschaft
 * @author     Thorsten Austen <thorsten@austen.eu.com>
 * @copyright  2025 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Vereinsmeisterschaft\Administrator\View\Vereinsmeisterschaftsteilnehmer;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Ttc\Component\Vereinsmeisterschaft\Administrator\Helper\VereinsmeisterschaftHelper;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
/**
 * View class for a list of Vereinsmeisterschaftsteilnehmer.
 *
 * @since  1.0.0
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
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = VereinsmeisterschaftHelper::getActions();

		ToolbarHelper::title(Text::_('COM_VEREINSMEISTERSCHAFT_TITLE_VEREINSMEISTERSCHAFTSTEILNEHMER'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Vereinsmeisterschaftsteilnehmer';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				$toolbar->addNew('vereinsmeisterschaftteilnahme.add');
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
				$childBar->publish('vereinsmeisterschaftsteilnehmer.publish')->listCheck(true);
				$childBar->unpublish('vereinsmeisterschaftsteilnehmer.unpublish')->listCheck(true);
				$childBar->archive('vereinsmeisterschaftsteilnehmer.archive')->listCheck(true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete('vereinsmeisterschaftsteilnehmer.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
			}

			$childBar->standardButton('duplicate')
				->text('JTOOLBAR_DUPLICATE')
				->icon('fas fa-copy')
				->task('vereinsmeisterschaftsteilnehmer.duplicate')
				->listCheck(true);

			if (isset($this->items[0]->checked_out))
			{
				$childBar->checkin('vereinsmeisterschaftsteilnehmer.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state))
			{
				$childBar->trash('vereinsmeisterschaftsteilnehmer.trash')->listCheck(true);
			}
		}

		

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{

			if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
			{
				$toolbar->delete('vereinsmeisterschaftsteilnehmer.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		if ($canDo->get('core.admin'))
		{
			$toolbar->preferences('com_vereinsmeisterschaft');
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_vereinsmeisterschaft&view=vereinsmeisterschaftsteilnehmer');
	}
	
	/**
	 * Method to order fields 
	 *
	 * @return void 
	 */
	protected function getSortFields()
	{
		return array(
			'a.`teilnehmer`' => Text::_('COM_VEREINSMEISTERSCHAFT_VEREINSMEISTERSCHAFTSTEILNEHMER_TEILNEHMER'),
			'a.`zusage`' => Text::_('COM_VEREINSMEISTERSCHAFT_VEREINSMEISTERSCHAFTSTEILNEHMER_ZUSAGE'),
			'a.`mitbringsel`' => Text::_('COM_VEREINSMEISTERSCHAFT_VEREINSMEISTERSCHAFTSTEILNEHMER_MITBRINGSEL'),
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
