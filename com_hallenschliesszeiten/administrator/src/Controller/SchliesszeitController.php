<?php
/**
 * @version    CVS: 1.3.0
 * @package    Com_Hallenschliesszeiten
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Hallenschliesszeiten\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Schliesszeit controller class.
 *
 * @since  1.3.0
 */
class SchliesszeitController extends FormController
{
	protected $view_list = 'schliesszeiten';
}
