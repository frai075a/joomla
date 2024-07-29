<?php
/**
 * @version    CVS: 1.0.2
 * @package    Com_Spielplan
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>

<form
	action="<?php echo Route::_('index.php?option=com_spielplan&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="spielplan-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'spielplan')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'spielplan', Text::_('COM_SPIELPLAN_TAB_SPIELPLAN', true)); ?>
	<div class="row-fluid">
		<div class="col-md-12 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo Text::_('COM_SPIELPLAN_FIELDSET_SPIELPLAN'); ?></legend>
				<?php echo $this->form->renderField('mannschaft'); ?>
				<?php echo $this->form->renderField('datum'); ?>
				<?php echo $this->form->renderField('uhrzeit'); ?>
				<?php echo $this->form->renderField('heimmannschaft'); ?>
				<?php echo $this->form->renderField('h_nummer'); ?>
				<?php echo $this->form->renderField('auswaertsmannschaft'); ?>
				<?php echo $this->form->renderField('a_nummer'); ?>
				<?php echo $this->form->renderField('ort'); ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>
	<input type="hidden" name="jform[id]" value="<?php echo isset($this->item->id) ? $this->item->id : ''; ?>" />


	
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
