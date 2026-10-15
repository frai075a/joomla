<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
$this->getDocument()->getWebAssetManager()->useScript('form.validate')->useScript('keepalive');
?>
<form id="adminForm" name="adminForm" method="post" class="form-validate" action="<?php echo Route::_('index.php?option=com_ttc_hallenbuchung&view=tttraining&layout=edit&id=' . (int) $this->item->id); ?>">
    <?php echo $this->form->renderField('datum'); ?>
    <?php echo $this->form->renderField('slot'); ?>
    <?php echo $this->form->renderField('paarung'); ?>
    <?php echo $this->form->renderField('state'); ?>
    <?php echo $this->form->getInput('id'); ?>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
