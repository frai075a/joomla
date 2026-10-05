<?php
/**
 * @version    CVS: 1.0.6
 * @package    Com_Spielplan
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Spielplanung\Administrator\View\Spielplaene;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Ttc\Component\Spielplanung\Administrator\Helper\SpielplanHelper;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
/**
 * View class for a list of Spielplaene.
 *
 * @since  1.0.6
 */
class HtmlView extends BaseHtmlView
{
	protected $items;

	protected $pagination;

	protected $state;
    public $filterForm;
    public $activeFilters;
    protected $sidebar;

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
	 * @since   1.0.6
	 */
	protected function addToolbar()
    {
        $canDo = SpielplanHelper::getActions();
        ToolbarHelper::title(Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_TITLE_SPIELPLAENE'), 'generic');
        $toolbar = $this->getDocument()->getToolbar();
        if ($canDo->get('core.create')) {
            $toolbar->addNew('spielplan.add');
            $toolbar->standardButton('duplicate')->text('JTOOLBAR_DUPLICATE')
                ->icon('fas fa-copy')->task('spielplaene.duplicate')->listCheck(true);
        }
        if ($canDo->get('core.delete')) {
            $toolbar->delete('spielplaene.delete')->text('JTOOLBAR_DELETE')
                ->message('JGLOBAL_CONFIRM_DELETE')->listCheck(true);
        }
        if ($canDo->get('core.admin')) {
            $toolbar->preferences('com_ttc_spielplanung');
        }
        Sidebar::setAction('index.php?option=com_ttc_spielplanung&view=spielplaene');
    }

/**
	 * Method to order fields 
	 *
	 * @return void 
	 */
	protected function getSortFields()
	{
		return array(
			'a.`mannschaft`' => Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_SPIELPLAENE_MANNSCHAFT'),
			'a.`datum`' => Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_SPIELPLAENE_DATUM'),
			'a.`uhrzeit`' => Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_SPIELPLAENE_UHRZEIT'),
			'a.`heimmannschaft`' => Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_SPIELPLAENE_HEIMMANNSCHAFT'),
			'a.`h_nummer`' => Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_SPIELPLAENE_H_NUMMER'),
			'a.`auswaertsmannschaft`' => Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_SPIELPLAENE_AUSWAERTSMANNSCHAFT'),
			'a.`a_nummer`' => Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_SPIELPLAENE_A_NUMMER'),
			'a.`ort`' => Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_SPIELPLAENE_ORT'),
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
