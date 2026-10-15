<?php
/**
 * @version    CVS: 2.1
 * @package    Com_Ttc_hallenfussball
 * @author     Thorsten Austen <fb@ttc-nordend.de>
 * @copyright  Copyright (C) 2013-2019. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

$canEdit = Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_ttc_hallenfussball');

if (!$canEdit && Factory::getApplication()->getIdentity()->authorise('core.edit.own', 'com_ttc_hallenfussball'))
{
	$canEdit = Factory::getApplication()->getIdentity()->id == $this->item->created_by;
}
?>

<div class="item_fields">
<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
    <?php endif;?>
	<table class="table">
		

		<tr>
			<th><?php echo Text::_('COM_TTC_HALLENFUSSBALL_FORM_LBL_HALLENFUSSBALLTEILNAHME_ID'); ?></th>
			<td><?php echo $this->item->id; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_TTC_HALLENFUSSBALL_FORM_LBL_HALLENFUSSBALLTEILNAHME_CREATED_BY'); ?></th>
			<td><?php echo $this->item->created_by_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_TTC_HALLENFUSSBALL_FORM_LBL_HALLENFUSSBALLTEILNAHME_CREATED_WHEN'); ?></th>
			<td><?php echo $this->item->created_when; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_TTC_HALLENFUSSBALL_FORM_LBL_HALLENFUSSBALLTEILNAHME_DATUM'); ?></th>
			<td><?php echo $this->item->datum; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_TTC_HALLENFUSSBALL_FORM_LBL_HALLENFUSSBALLTEILNAHME_TEILNEHMER'); ?></th>
			<td><?php echo $this->item->teilnehmer; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_TTC_HALLENFUSSBALL_FORM_LBL_HALLENFUSSBALLTEILNAHME_ZUSAGE'); ?></th>
			<td>

			<?php if (!empty($this->item->zusage) || $this->item->zusage === 0)
			{
					 echo Text::_('COM_TTC_HALLENFUSSBALL_HALLENFUSSBALLTEILNEHMER_ZUSAGE_OPTION_' . strtoupper(str_replace(' ', '_',$this->item->zusage)));
			}
			?></td>
		</tr>

	</table>

</div>

<?php if($canEdit): ?>

	<a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_ttc_hallenfussball&task=hallenfussballteilnahme.edit&id='.$this->item->id); ?>"><?php echo Text::_("COM_TTC_HALLENFUSSBALL_EDIT_ITEM"); ?></a>

<?php endif; ?>

<?php if (Factory::getApplication()->getIdentity()->authorise('core.delete','com_ttc_hallenfussball.hallenfussballteilnahme.'.$this->item->id)) : ?>

	<a class="btn btn-danger" rel="noopener noreferrer" href="#deleteModal" role="button" data-bs-toggle="modal">
		<?php echo Text::_("COM_TTC_HALLENFUSSBALL_DELETE_ITEM"); ?>
	</a>

	<?php echo HTMLHelper::_(
                                    'bootstrap.renderModal',
                                    'deleteModal',
                                    array(
                                        'title'  => Text::_('COM_TTC_HALLENFUSSBALL_DELETE_ITEM'),
                                        'height' => '50%',
                                        'width'  => '20%',
                                        
                                        'modalWidth'  => '50',
                                        'bodyHeight'  => '100',
                                        'footer' => '<button class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button><a href="' . Route::_('index.php?option=com_ttc_hallenfussball&task=hallenfussballteilnahme.remove&id=' . $this->item->id, false, 2) .'" class="btn btn-danger">' . Text::_('COM_TTC_HALLENFUSSBALL_DELETE_ITEM') .'</a>'
                                    ),
                                    Text::sprintf('COM_TTC_HALLENFUSSBALL_DELETE_CONFIRM', $this->item->id)
                                ); ?>

<?php endif; ?>