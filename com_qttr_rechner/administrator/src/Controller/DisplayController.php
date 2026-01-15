<?php

/**
 * @version    CVS: 2.1.0
 * @package    Com_Qttr_rechner
 * @author     Doganer/Austen <fb@ttc-nordend.de>
 * @copyright  2022 Thorsten
 * @license    GNU General Public License Version 2 oder später; siehe LICENSE.txt
 */

namespace Qttr\Component\Qttr_rechner\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Qttr_rechner master display controller.
 *
 * @since  2.1.0
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  2.1.0
	 */
	protected $default_view = 'spiele';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return  BaseController|boolean  This object to support chaining.
	 *
	 * @since   2.1.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		return parent::display();
	}
}
