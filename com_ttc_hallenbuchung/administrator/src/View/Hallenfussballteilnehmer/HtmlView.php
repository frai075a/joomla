<?php
/**
 * @version    CVS: 2.1
 * @package    Com_Ttc_hallenbuchung
 * @author     Thorsten Austen <fb@ttc-nordend.de>
 * @copyright  Copyright (C) 2013-2019. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\View\Hallenfussballteilnehmer;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Ttchallenbuchung\Component\Ttc_hallenbuchung\Administrator\Helper\Ttc_hallenbuchungHelper;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
/**
 * View class for a list of Hallenfussballteilnehmer.
 *
 * @since  2.1
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

		$this->addToolbar();

		$this->sidebar = Sidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.1
	 */
	protected function addToolbar()
	{
		$state = $this->getModel()->getState();
		$canDo = Ttc_hallenbuchungHelper::getActions();

		ToolbarHelper::title(Text::_('COM_TTC_HALLENBUCHUNG_TITLE_HALLENFUSSBALLTEILNEHMER'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Hallenfussballteilnehmer';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				$toolbar->addNew('hallenfussballteilnahme.add');
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
				$childBar->publish('hallenfussballteilnehmer.publish')->listCheck(true);
				$childBar->unpublish('hallenfussballteilnehmer.unpublish')->listCheck(true);
				$childBar->archive('hallenfussballteilnehmer.archive')->listCheck(true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete('hallenfussballteilnehmer.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
			}

			$childBar->standardButton('duplicate')
				->text('JTOOLBAR_DUPLICATE')
				->icon('fas fa-copy')
				->task('hallenfussballteilnehmer.duplicate')
				->listCheck(true);

			if (isset($this->items[0]->checked_out))
			{
				$childBar->checkin('hallenfussballteilnehmer.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state))
			{
				$childBar->trash('hallenfussballteilnehmer.trash')->listCheck(true);
			}
		}

		

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{

			if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
			{
				$toolbar->delete('hallenfussballteilnehmer.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		if ($canDo->get('core.admin'))
		{
			$toolbar->preferences('com_ttc_hallenbuchung');
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_ttc_hallenbuchung&view=hallenfussballteilnehmer');
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
			'a.`created_by`' => Text::_('COM_TTC_HALLENBUCHUNG_HALLENFUSSBALLTEILNEHMER_CREATED_BY'),
			'a.`datum`' => Text::_('COM_TTC_HALLENBUCHUNG_HALLENFUSSBALLTEILNEHMER_DATUM'),
			'a.`teilnehmer`' => Text::_('COM_TTC_HALLENBUCHUNG_HALLENFUSSBALLTEILNEHMER_TEILNEHMER'),
			'a.`zusage`' => Text::_('COM_TTC_HALLENBUCHUNG_HALLENFUSSBALLTEILNEHMER_ZUSAGE'),
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
