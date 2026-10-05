<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();
$user = Factory::getApplication()->getIdentity();
$this->getDocument()->getWebAssetManager()->useScript('core');
?>

<form action="<?php echo Route::_('index.php?option=com_ttc_spielplanung&view=kategorien'); ?>" method="post" name="adminForm" id="adminForm">

    <div class="row">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo Text::_('COM_TTC_SPIELPLANUNG_TITLE_KATEGORIEN'); ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width: 30px;">
                                    <input type="checkbox" id="checkall" name="checkall" value="" class="cat-checkall" />
                                </th>
                                <th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
                                <th style="width: 150px;"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_ORDERING'); ?> <span class="text-danger">*</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($this->items) : ?>
                                <?php foreach ($this->items as $item) : ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="kategorien[<?php echo (int) $item->id; ?>][checked]" value="0" />
                                            <input type="checkbox" name="kategorien[<?php echo (int) $item->id; ?>][checked]" value="1" <?php echo $item->is_selected ? 'checked="checked"' : ''; ?> class="cat-checkbox" data-cat-id="<?php echo (int) $item->id; ?>" />
                                        </td>
                                        <td>
                                            <span style="padding-left: <?php echo (int) $item->level * 20; ?>px;">
                                                <?php echo HTMLHelper::_('string.truncate', $item->title, 50); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <input type="number" name="kategorien[<?php echo (int) $item->id; ?>][sort_order]" value="<?php echo (int) $item->sort_order ?: ''; ?>" class="form-control form-control-sm sort-order-input" min="1" max="99" placeholder="—" data-cat-id="<?php echo (int) $item->id; ?>" <?php echo $item->is_selected ? '' : 'disabled="disabled"'; ?> />
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">
                                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php echo $this->pagination->getListFooter(); ?>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" data-save-task="kategorien.save">
        <?php echo Text::_('JSAVE'); ?>
    </button>

    <input type="hidden" name="kategorien_complete" value="1" />
    <input type="hidden" name="view" value="kategorien" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo HTMLHelper::_('form.token'); ?>

</form>

<script>
(function() {
    const form = document.getElementById('adminForm');
    const checkboxes = document.querySelectorAll('.cat-checkbox');

    document.querySelector('.cat-checkall').addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            checkbox.dispatchEvent(new Event('change'));
        });
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const catId = this.getAttribute('data-cat-id');
            const sortInput = document.querySelector('input.sort-order-input[data-cat-id="' + catId + '"]');
            if (this.checked) {
                sortInput.removeAttribute('disabled');
            } else {
                sortInput.setAttribute('disabled', 'disabled');
                sortInput.value = '';
            }
        });
    });

    form.addEventListener('submit', function(e) {
        this.task.value = e.submitter ? (e.submitter.dataset.saveTask || '') : '';
        if (this.task.value !== 'kategorien.save') {
            return;
        }

        let hasError = false;
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const catId = checkbox.getAttribute('data-cat-id');
                const sortInput = document.querySelector('input.sort-order-input[data-cat-id="' + catId + '"]');
                if (!sortInput.value || sortInput.value < 1 || sortInput.value > 99) {
                    sortInput.classList.add('is-invalid');
                    hasError = true;
                } else {
                    sortInput.classList.remove('is-invalid');
                }
            }
        });
        if (hasError) {
            e.preventDefault();
            alert('<?php echo Text::_('COM_TTC_SPIELPLANUNG_ORDERING_REQUIRED'); ?>');
            return false;
        }
    });
})();
</script>
