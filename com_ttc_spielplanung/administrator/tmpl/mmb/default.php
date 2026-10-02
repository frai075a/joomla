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

<form action="<?php echo Route::_('index.php?option=com_ttc_spielplanung&view=mmb'); ?>" method="post" name="adminForm" id="adminForm">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo Text::_('COM_TTC_SPIELPLANUNG_TITLE_MMB'); ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 200px;"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_PLAYER'); ?></th>
                                <th style="width: 150px;"><?php echo Text::_('JGLOBAL_USERNAME'); ?></th>
                                <th style="width: 200px;"><?php echo Text::_('JGLOBAL_EMAIL'); ?></th>
                                <th style="width: 180px;"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_CATEGORY'); ?></th>
                                <th style="width: 150px;"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_POSITION'); ?></th>
                                <th style="width: 120px;" class="text-center"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_CAPTAIN'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($this->items) : ?>
                                <?php foreach ($this->items as $item) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item->name); ?></td>
                                        <td><?php echo htmlspecialchars($item->username); ?></td>
                                        <td><?php echo htmlspecialchars($item->email); ?></td>
                                        <td>
                                            <select name="mmb[<?php echo (int) $item->id; ?>][category_id]" class="form-select form-select-sm mmb-category-select">
                                                <option value=""><?php echo Text::_('COM_TTC_SPIELPLANUNG_SELECT_MANNSCHAFTSZUORDNUNG'); ?></option>
                                                <?php foreach ($this->categories as $catId => $cat) : ?>
                                                    <option value="<?php echo (int) $catId; ?>" <?php echo $item->category_id == $catId ? 'selected="selected"' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat['title']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <button type="button" class="btn btn-sm btn-outline-secondary moveup-btn" data-user-id="<?php echo (int) $item->id; ?>" data-category-id="<?php echo (int) $item->category_id; ?>" title="<?php echo Text::_('COM_TTC_SPIELPLANUNG_MOVE_UP'); ?>" <?php echo $item->category_id ? '' : 'disabled="disabled"'; ?>>
                                                    <span class="icon-chevron-up"></span>
                                                </button>
                                                <input type="number" name="mmb[<?php echo (int) $item->id; ?>][position]" value="<?php echo $item->category_id && (int) $item->position > 0 ? (int) $item->position : ''; ?>" class="form-control form-control-sm text-center mmb-position" <?php echo $item->category_id ? '' : 'disabled="disabled"'; ?> min="1" max="99" placeholder="—" />
                                                <button type="button" class="btn btn-sm btn-outline-secondary movedown-btn" data-user-id="<?php echo (int) $item->id; ?>" data-category-id="<?php echo (int) $item->category_id; ?>" title="<?php echo Text::_('COM_TTC_SPIELPLANUNG_MOVE_DOWN'); ?>" <?php echo $item->category_id ? '' : 'disabled="disabled"'; ?>>
                                                    <span class="icon-chevron-down"></span>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="mmb[<?php echo (int) $item->id; ?>][is_captain]" value="1" class="form-check-input mmb-captain-checkbox" <?php echo $item->is_captain ? 'checked="checked"' : ''; ?> title="<?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_CAPTAIN'); ?>" <?php echo $item->category_id ? '' : 'disabled="disabled"'; ?> />
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
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

    <button type="submit" class="btn btn-primary" data-save-task="mmb.save">
        <?php echo Text::_('JSAVE'); ?>
    </button>

    <input type="hidden" name="view" value="mmb" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo HTMLHelper::_('form.token'); ?>

</form>

<script>
(function() {
    const form = document.getElementById('adminForm');

    document.querySelectorAll('.moveup-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const categoryId = this.getAttribute('data-category-id');
            if (!categoryId) {
                alert('<?php echo Text::_('COM_TTC_SPIELPLANUNG_SELECT_CATEGORY_FIRST'); ?>');
                return;
            }
            movePosition(userId, categoryId, 'up');
        });
    });

    document.querySelectorAll('.movedown-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const categoryId = this.getAttribute('data-category-id');
            if (!categoryId) {
                alert('<?php echo Text::_('COM_TTC_SPIELPLANUNG_SELECT_CATEGORY_FIRST'); ?>');
                return;
            }
            movePosition(userId, categoryId, 'down');
        });
    });

    function movePosition(userId, categoryId, direction) {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('category_id', categoryId);
        formData.append('direction', direction);
        formData.append('<?php echo Factory::getApplication()->getSession()->getFormToken(); ?>', '1');

        fetch('<?php echo Route::_('index.php?option=com_ttc_spielplanung&task=mmb.movePosition', false); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => window.location.reload())
        .catch(error => console.error('Error:', error));
    }

    form.addEventListener('submit', function(e) {
        this.task.value = e.submitter ? (e.submitter.dataset.saveTask || '') : '';
    });

    document.querySelectorAll('.mmb-captain-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!this.checked) {
                return;
            }

            const row = this.closest('tr');
            const categorySelect = row.querySelector('.mmb-category-select');
            const categoryId = categorySelect ? categorySelect.value : '';

            if (!categoryId) {
                this.checked = false;
                return;
            }

            document.querySelectorAll('.mmb-captain-checkbox').forEach(other => {
                if (other === this) {
                    return;
                }

                const otherRow = other.closest('tr');
                const otherSelect = otherRow.querySelector('.mmb-category-select');

                if (otherSelect && otherSelect.value === categoryId) {
                    other.checked = false;
                }
            });
        });
    });

    document.querySelectorAll('.mmb-category-select').forEach(select => {
        function updateAssignment() {
            const row = select.closest('tr');
            const position = row.querySelector('.mmb-position');
            const checkbox = row.querySelector('.mmb-captain-checkbox');
            const assigned = select.value !== '';

            position.disabled = !assigned;
            checkbox.disabled = !assigned;
            if (!assigned) {
                position.value = '';
                checkbox.checked = false;
            }

            // Movement operates on saved assignments; save a changed team first.
            row.querySelectorAll('.moveup-btn, .movedown-btn').forEach(button => {
                button.disabled = !assigned || button.getAttribute('data-category-id') !== select.value;
            });
        }

        select.addEventListener('change', updateAssignment);
        updateAssignment();
    });
})();
</script>
