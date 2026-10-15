<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Vereinsmeisterschaft
 * @author     Thorsten Austen <thorsten@austen.eu.com>
 * @copyright  2025 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Component\Vereinsmeisterschaft\Administrator\Service\Html;

// No direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\CMS\Factory\DatabaseFactory;
//noch notwendig?
use Joomla\Database\DatabaseDriver;

/**
 * Vereinsmeisterschaft HTML Helper.
 *
 * @since  1.0.0
 */
class VEREINSMEISTERSCHAFT
{
	use DatabaseAwareTrait;

	/**
	 * Public constructor.
	 *
	 * @param   DatabaseDriver  $db  The Joomla DB driver object for the site's database.
	 */
	public function __construct(DatabaseInterface $database)
	{
        $this->setDatabase($database);
    }
	public function toggle($value = 0, $view='', $field='', $i='')
	{
		$states = array(
			0 => array('icon-unpublish', Text::_('Toggle'), ''),
			1 => array('icon-publish', Text::_('Toggle'), '')
		);

		$state  = ArrayHelper::getValue($states, (int) $value, $states[0]);
		$text   = '<span aria-hidden="true" class="' . $state[0] . '"></span>';
		$html   = '<a href="javascript:void(0);" class="tbody-icon ' . $state[2] . '"';
		$html  .= 'onclick="return Joomla.toggleField(\'cb'.$i.'\',\'' . $view . '.toggle\',\'' . $field . '\')" title="' . Text::_($state[1]) . '">' . $text . '</a>';

		return $html;
	}
}
