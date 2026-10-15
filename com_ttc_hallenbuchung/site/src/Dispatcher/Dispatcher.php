<?php
/**
 * @version    CVS: 2.1
 * @package    Com_Ttc_hallenbuchung
 * @author     Thorsten Austen <fb@ttc-nordend.de>
 * @copyright  Copyright (C) 2013-2019. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

/**
 * ComponentDispatcher class for Com_Ttc_hallenbuchung
 *
 * @since  2.1
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   2.1
	 */
	public function dispatch()
	{
		parent::dispatch();
	}
}
