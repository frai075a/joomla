<?php
/**
 * @version    CVS: 1.2.0
 * @package    Com_Hallenschliesszeiten
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Hallenschliesszeiten\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

/**
 * ComponentDispatcher class for Com_Hallenschliesszeiten
 *
 * @since  1.2.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function dispatch()
	{
		parent::dispatch();
	}
}
