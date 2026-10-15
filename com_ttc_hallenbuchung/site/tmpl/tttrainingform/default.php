<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
$this->getDocument()->getWebAssetManager()->useScript('form.validate')->useScript('keepalive')->useStyle('com_ttc_hallenbuchung.form');
?>
<div class="hallenfussballteilnahme-edit">
<h1><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_BOOK'); ?></h1>
<p><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_INTRO'); ?></p>
<form id="tttraining-form" method="post" class="form-validate" action="<?php echo Route::_('index.php?option=com_ttc_hallenbuchung'); ?>">
    <?php echo $this->form->renderField('datum'); ?>
    <?php echo $this->form->renderField('slot'); ?>
    <?php echo $this->form->renderField('paarung'); ?>
    <?php echo $this->form->getInput('id'); ?>
    <div class="hallenfussball-form-actions">
        <button class="btn btn-primary validate" type="submit" name="task" value="tttrainingform.save"><?php echo Text::_('JSAVE'); ?></button>
        <button class="btn btn-secondary" type="submit" formnovalidate name="task" value="tttrainingform.cancel"><?php echo Text::_('JCANCEL'); ?></button>
    </div>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
</div>
