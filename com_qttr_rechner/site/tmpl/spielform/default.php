<?php
/**
 * @version    CVS: 2.1.0
 * @package    Com_Qttr_rechner
 * @author     Doganer/Austen <fb@ttc-nordend.de>
 * @copyright  2022 Thorsten
 * @license    GNU General Public License Version 2 oder später; siehe LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Qttr\Component\Qttr_rechner\Site\Helper\Qttr_rechnerHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_qttr_rechner', JPATH_SITE);

$user    = Factory::getUser();
$canEdit = Qttr_rechnerHelper::canUserEdit($this->item, $user);


?>

<div class="spiel-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new \Exception(Text::_('COM_QTTR_RECHNER_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo Text::sprintf('COM_QTTR_RECHNER_EDIT_ITEM_TITLE', $this->item->id); ?></h1>
		<?php else: ?>
			<h1><?php echo Text::_('COM_QTTR_RECHNER_ADD_ITEM_TITLE'); ?></h1>
		<?php endif; ?>

		<form id="form-spiel"
			  action="<?php echo Route::_('index.php?option=com_qttr_rechner&task=spielform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
	<input type="hidden" name="jform[id]" value="<?php echo isset($this->item->id) ? $this->item->id : ''; ?>" />

	<input type="hidden" name="jform[state]" value="<?php echo isset($this->item->state) ? $this->item->state : ''; ?>" />

	<input type="hidden" name="jform[ordering]" value="<?php echo isset($this->item->ordering) ? $this->item->ordering : ''; ?>" />

	<input type="hidden" name="jform[checked_out]" value="<?php echo isset($this->item->checked_out) ? $this->item->checked_out : ''; ?>" />

	<input type="hidden" name="jform[checked_out_time]" value="<?php echo isset($this->item->checked_out_time) ? $this->item->checked_out_time : ''; ?>" />

				<?php echo $this->form->getInput('created_by'); ?>
				<?php echo $this->form->getInput('modified_by'); ?>
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'spiel')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'spiel', Text::_('COM_QTTR_RECHNER_TAB_SPIEL', true)); ?>
	<?php echo $this->form->renderField('qttr_wert'); ?>

	<?php echo $this->form->renderField('sieg'); ?>

	<?php echo $this->form->renderField('letztesspiel'); ?>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-danger"
					   href="<?php echo Route::_('index.php?option=com_qttr_rechner&task=spielform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span>
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_qttr_rechner"/>
			<input type="hidden" name="task"
				   value="spielform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
