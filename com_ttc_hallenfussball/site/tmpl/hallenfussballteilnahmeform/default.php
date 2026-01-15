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
use \Ttchallenfussball\Component\Ttc_hallenfussball\Site\Helper\Ttc_hallenfussballHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_ttc_hallenfussball', JPATH_SITE);

$user    = Factory::getApplication()->getIdentity();
$canEdit = Ttc_hallenfussballHelper::canUserEdit($this->item, $user);


?>

<div class="hallenfussballteilnahme-edit front-end-edit">

<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
    <?php endif;?>
	<?php if (!$canEdit) : ?>
		<h3>
		<?php throw new \Exception(Text::_('COM_TTC_HALLENFUSSBALL_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo Text::sprintf('COM_TTC_HALLENFUSSBALL_EDIT_ITEM_TITLE', $this->item->id); ?></h1>
		<?php else: ?>
			<h1><?php echo Text::_('COM_TTC_HALLENFUSSBALL_ADD_ITEM_TITLE'); ?></h1>
		<?php endif; ?>

		<form id="form-hallenfussballteilnahme"
			  action="<?php echo Route::_('index.php?option=com_ttc_hallenfussball&task=hallenfussballteilnahmeform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'hallenfussballteilnahme')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'hallenfussballteilnahme', Text::_('COM_TTC_HALLENFUSSBALL_TAB_HALLENFUSSBALLTEILNAHME', true)); ?>
	<?php echo $this->form->renderField('id'); ?>

	<?php echo $this->form->renderField('created_by'); ?>

	<?php echo $this->form->renderField('created_when'); ?>

	<?php echo $this->form->renderField('datum'); ?>

	<?php echo $this->form->renderField('teilnehmer'); ?>

	<?php echo $this->form->renderField('zusage'); ?>

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
					   href="<?php echo Route::_('index.php?option=com_ttc_hallenfussball&task=hallenfussballteilnahmeform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span>
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_ttc_hallenfussball"/>
			<input type="hidden" name="task"
				   value="hallenfussballteilnahmeform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
