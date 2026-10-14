<?php

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Ttc\Component\Hallenschliesszeiten\Site\Helper\HalleninfosHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('form.validate')
	->useScript('form.validate');


// Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_hallenschliesszeiten', JPATH_SITE);

$user    = Factory::getApplication()->getIdentity();
$canEdit = HalleninfosHelper::canUserEdit($this->item, $user);

//TA: get Username
$username   = $user->name;
$name_array = preg_split("/[\s,]+/", $username);
echo '<h1>Hallo ' . $this->escape($user->name) . '!</h1>';
?>
Hier kannst du Halleninfos verfassen, die dann auch ohne Buchungen auf der Übersichtsseite angezeigt werden. Das können z.B. Infos sein, wie "Ein Heimspiel, bitte Platz lassen".
<br><br>
<p><img src="media/com_hallenschliesszeiten/halleninfoerklaerung.jpg" width="430" height="162" loading="lazy"></p>

<div class="halleninfo-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
		<?php throw new \Exception(Text::_('COM_HALLENINFOS_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo Text::sprintf('COM_HALLENINFOS_EDIT_ITEM_TITLE', $this->item->id); ?></h1>
		<?php else: ?>
			<h1><?php echo Text::_('COM_HALLENINFOS_ADD_ITEM_TITLE'); ?></h1>
		<?php endif; ?>

		<form id="form-halleninfo"
			  action="<?php echo Route::_('index.php?option=com_hallenschliesszeiten&task=halleninfoform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
	<input type="hidden" name="jform[id]" value="<?php echo isset($this->item->id) ? $this->item->id : ''; ?>" />

	<input type="hidden" name="jform[state]" value="<?php echo isset($this->item->state) ? $this->item->state : ''; ?>" />

				<?php echo $this->form->getInput('created_by'); ?>
				<?php echo $this->form->getInput('modified_by'); ?>
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'halleninfo')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'halleninfo', Text::_('COM_HALLENINFOS_TAB_HALLENINFO', true)); ?>
	<?php echo $this->form->renderField('datum'); ?>

	<?php echo $this->form->renderField('information'); ?>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-danger"
					   href="<?php echo Route::_('index.php?option=com_hallenschliesszeiten&task=halleninfoform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span>
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_hallenschliesszeiten"/>
			<input type="hidden" name="task"
				   value="halleninfoform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
