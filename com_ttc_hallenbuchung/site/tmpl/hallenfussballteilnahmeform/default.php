<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Helper\Ttc_hallenbuchungHelper;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')->useScript('form.validate')->useStyle('com_ttc_hallenbuchung.form');
$user = Factory::getApplication()->getIdentity();
if (!Ttc_hallenbuchungHelper::canUserEdit($this->item)) {
    throw new \Exception(Text::_('COM_TTC_HALLENBUCHUNG_ERROR_MESSAGE_NOT_AUTHORISED'), 403);
}
$createdWhen = !empty($this->item->created_when) ? $this->item->created_when : Factory::getDate()->toSql();
?>
<div class="hallenfussballteilnahme-edit front-end-edit">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header"><h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1></div>
    <?php endif; ?>
    <h2><?php echo Text::_(empty($this->item->id) ? 'COM_TTC_HALLENBUCHUNG_ADD_ITEM_TITLE' : 'COM_TTC_HALLENBUCHUNG_EDIT_ITEM_TITLE'); ?></h2>
    <form id="form-hallenfussballteilnahme"
          action="<?php echo Route::_('index.php?option=com_ttc_hallenbuchung&task=hallenfussballteilnahmeform.save'); ?>"
          method="post" class="form-validate">
        <?php echo $this->form->renderField('datum'); ?>
        <?php echo $this->form->renderField('zusage'); ?>
        <?php echo $this->form->renderField('teilnehmer'); ?>
        <div class="hallenfussball-form-actions">
            <?php if ($this->canSave) : ?>
                <button type="submit" class="validate btn btn-primary">
                    <?php echo Text::_('COM_TTC_HALLENBUCHUNG_SAVE_PARTICIPATION'); ?>
                </button>
            <?php endif; ?>
            <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_ttc_hallenbuchung&task=hallenfussballteilnahmeform.cancel'); ?>">
                <?php echo Text::_('JCANCEL'); ?>
            </a>
        </div>
        <p class="hallenfussball-created">
            <?php echo Text::_('COM_TTC_HALLENBUCHUNG_FORM_LBL_HALLENFUSSBALLTEILNAHME_CREATED_WHEN'); ?>:
            <?php echo HTMLHelper::_('date', $createdWhen, 'd.m.Y H:i'); ?>
        </p>
        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="option" value="com_ttc_hallenbuchung">
        <input type="hidden" name="task" value="hallenfussballteilnahmeform.save">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
