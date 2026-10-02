<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$user = Factory::getApplication()->getIdentity();
?>

<style>
    .com-ttc-spielplanung-status-switch .form-check-input[role="switch"] {
        width: 3rem;
        height: 1.6rem;
        cursor: pointer;
        background-color: #dc3545;
        border-color: #dc3545;
    }
    .com-ttc-spielplanung-status-switch .form-check-input[role="switch"]:checked {
        background-color: #198754;
        border-color: #198754;
    }
    .com-ttc-spielplanung-status-switch .form-check-input[role="switch"]:focus {
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }
    .com-ttc-spielplanung-status-switch .form-check-label {
        margin-left: 0.5rem;
        vertical-align: middle;
        font-weight: 500;
    }
    .com-ttc-spielplanung-filter-box {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 2rem;
        align-items: center;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
    }
</style>

<div class="com-ttc-spielplanung-frontend">
    <h1><?php echo Text::_('COM_TTC_SPIELPLANUNG_TITLE_SPIELPLAN'); ?></h1>

    <form action="<?php echo Route::_('index.php?option=com_ttc_spielplanung'); ?>" method="get" name="spielplanFilterForm" id="spielplanFilterForm" class="com-ttc-spielplanung-filter-box">
        <input type="hidden" name="option" value="com_ttc_spielplanung" />
        <input type="hidden" name="only_future_submitted" value="1" />
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="only_future" name="only_future" value="1" <?php echo $this->onlyFuture ? 'checked="checked"' : ''; ?> onchange="this.form.submit();" />
            <label class="form-check-label" for="only_future">
                <?php echo Text::_('COM_TTC_SPIELPLANUNG_FILTER_ONLY_FUTURE'); ?>
            </label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="vorrunde" name="vorrunde" value="1" <?php echo $this->vorrundeOnly ? 'checked="checked"' : ''; ?> onchange="this.form.submit();" />
            <label class="form-check-label" for="vorrunde">
                <?php echo Text::_('COM_TTC_SPIELPLANUNG_FILTER_VORRUNDE'); ?>
            </label>
        </div>
    </form>

    <?php if ($this->games) : ?>
        <form action="<?php echo Route::_('index.php?option=com_ttc_spielplanung&task=display.saveGame'); ?>" method="post" name="spielplanForm" id="spielplanForm">

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_SPIELDATUM'); ?></th>
                            <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_GEGNER'); ?></th>
                            <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_SPORTHALLE'); ?></th>
                            <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_STATUS'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->games as $game) : ?>
                            <tr>
                                <td><?php echo HTMLHelper::_('date', $game['spieldatum'] . ' ' . $game['uhrzeit'], 'd.m.Y H:i'); ?></td>
                                <td><?php echo htmlspecialchars($game['gegner'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($game['sporthalle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php $statusChecked = ($game['status'] === null || $game['status'] == 1); ?>
                                    <div class="form-check form-switch com-ttc-spielplanung-status-switch"
                                         data-label-yes="<?php echo htmlspecialchars(Text::_('COM_TTC_SPIELPLANUNG_STATUS_YES'), ENT_QUOTES, 'UTF-8'); ?>"
                                         data-label-no="<?php echo htmlspecialchars(Text::_('COM_TTC_SPIELPLANUNG_STATUS_NO'), ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="status_<?php echo (int) $game['game_id']; ?>" value="0" />
                                        <input type="checkbox" class="form-check-input" role="switch" id="status_<?php echo (int) $game['game_id']; ?>" name="status_<?php echo (int) $game['game_id']; ?>" value="1" aria-checked="<?php echo $statusChecked ? 'true' : 'false'; ?>" <?php echo $statusChecked ? 'checked="checked"' : ''; ?> />
                                        <label class="form-check-label" for="status_<?php echo (int) $game['game_id']; ?>">
                                            <?php echo $statusChecked ? Text::_('COM_TTC_SPIELPLANUNG_STATUS_YES') : Text::_('COM_TTC_SPIELPLANUNG_STATUS_NO'); ?>
                                        </label>
                                    </div>
                                    <input type="hidden" name="game_ids[]" value="<?php echo (int) $game['game_id']; ?>" />
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" name="task" value="display.saveGame" class="btn btn-primary">
                <?php echo Text::_('JSAVE'); ?>
            </button>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    <?php else : ?>
        <div class="alert alert-info">
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.com-ttc-spielplanung-status-switch .form-check-input[role="switch"]').forEach(function (input) {
        input.addEventListener('change', function () {
            var wrapper = input.closest('.com-ttc-spielplanung-status-switch');
            var label = wrapper.querySelector('.form-check-label');
            input.setAttribute('aria-checked', input.checked ? 'true' : 'false');
            label.textContent = input.checked ? wrapper.dataset.labelYes : wrapper.dataset.labelNo;
        });
    });
</script>
