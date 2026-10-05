<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$this->getDocument()->getWebAssetManager()->useScript('core');

$escape = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$userName = static function ($name, $id) {
    return $name ?: ((int) $id ? Text::sprintf('COM_TTC_SPIELPLANUNG_MISSING_USER', (int) $id) : '—');
};
$date = static function ($value) {
    return !$value || strpos($value, '0000-00-00') === 0 ? '—' : HTMLHelper::_('date', $value, 'd.m.Y H:i');
};
$confirm = static function ($key) use ($escape) {
    return $escape('return window.confirm(' . json_encode(Text::_($key), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ');');
};
$action = Route::_('index.php?option=com_ttc_spielplanung&view=eintraege');
?>
<div class="card">
    <div class="card-header"><h3><?php echo Text::_('COM_TTC_SPIELPLANUNG_TITLE_EINTRAEGE'); ?></h3></div>
    <div class="card-body">
        <form action="<?php echo $action; ?>" method="get" class="row g-3 mb-3">
            <input type="hidden" name="option" value="com_ttc_spielplanung">
            <input type="hidden" name="view" value="eintraege">
            <input type="hidden" name="limitstart" value="0">
            <div class="col-md-4">
                <label for="entries_player" class="form-label"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_PLAYER'); ?></label>
                <select name="entries_player" id="entries_player" class="form-select">
                    <option value="0"><?php echo Text::_('COM_TTC_SPIELPLANUNG_ALL_PLAYERS'); ?></option>
                    <?php foreach ($this->players as $player) : ?>
                        <option value="<?php echo (int) $player['user_id']; ?>" <?php echo $this->playerId === (int) $player['user_id'] ? 'selected' : ''; ?>><?php echo $escape($userName($player['player_name'], $player['user_id'])); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="entries_sort" class="form-label"><?php echo Text::_('COM_TTC_SPIELPLANUNG_SORT_BY'); ?></label>
                <select name="entries_sort" id="entries_sort" class="form-select">
                    <?php foreach (['player' => 'PLAYER', 'match' => 'MATCH', 'date' => 'SPIELDATUM'] as $value => $label) : ?>
                        <option value="<?php echo $value; ?>" <?php echo $this->sort === $value ? 'selected' : ''; ?>><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_' . $label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="entries_direction" class="form-label"><?php echo Text::_('COM_TTC_SPIELPLANUNG_SORT_DIRECTION'); ?></label>
                <select name="entries_direction" id="entries_direction" class="form-select">
                    <?php foreach (['ASC', 'DESC'] as $value) : ?>
                        <option value="<?php echo $value; ?>" <?php echo $this->direction === $value ? 'selected' : ''; ?>><?php echo Text::_('COM_TTC_SPIELPLANUNG_SORT_' . $value); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary"><?php echo Text::_('COM_TTC_SPIELPLANUNG_APPLY_FILTER'); ?></button>
            </div>
        </form>

        <?php if ($this->canDelete) : ?>
            <form action="<?php echo $action; ?>" method="post" class="mb-3" onsubmit="<?php echo $confirm('COM_TTC_SPIELPLANUNG_CONFIRM_DELETE_ALL'); ?>">
                <input type="hidden" name="task" value="eintraege.removeAll">
                <button type="submit" class="btn btn-danger"><?php echo Text::_('COM_TTC_SPIELPLANUNG_DELETE_ALL'); ?></button>
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr>
                    <?php foreach (['ID', 'PLAYER', 'MATCH', 'SPIELDATUM', 'STATUS', 'STATE', 'ACTIONS'] as $field) : ?>
                        <th scope="col"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_' . $field); ?></th>
                    <?php endforeach; ?>
                </tr></thead>
                <tbody>
                    <?php foreach ($this->items as $item) :
                        $tooltip = Text::_('COM_TTC_SPIELPLANUNG_CREATED') . ': ' . $date($item->created)
                            . "\n" . Text::_('COM_TTC_SPIELPLANUNG_CREATED_BY') . ': ' . $userName($item->creator_name, $item->created_by)
                            . "\n" . Text::_('COM_TTC_SPIELPLANUNG_MODIFIED') . ': ' . $date($item->modified)
                            . "\n" . Text::_('COM_TTC_SPIELPLANUNG_MODIFIED_BY') . ': ' . $userName($item->modifier_name, $item->modified_by);
                        $match = $item->heimmannschaft !== null && $item->auswaertsmannschaft !== null
                            ? trim($item->heimmannschaft . ' ' . ($item->h_nummer ?? '')) . ' - ' . trim($item->auswaertsmannschaft . ' ' . ($item->a_nummer ?? ''))
                            : Text::sprintf('COM_TTC_SPIELPLANUNG_MISSING_MATCH', (int) $item->game_id);
                    ?>
                        <tr title="<?php echo $escape($tooltip); ?>" tabindex="0">
                            <td><?php echo (int) $item->id; ?></td>
                            <td><?php echo $escape($userName($item->player_name, $item->user_id)); ?></td>
                            <td><?php echo $escape($match); ?></td>
                            <td><?php echo $escape($item->game_date ? HTMLHelper::_('date', $item->game_date, 'd.m.Y') : '—'); ?></td>
                            <td><?php echo $item->status === null ? Text::_('COM_TTC_SPIELPLANUNG_STATUS_NEUTRAL') : ((int) $item->status === 1 ? Text::_('COM_TTC_SPIELPLANUNG_STATUS_YES') : Text::_('COM_TTC_SPIELPLANUNG_STATUS_NO')); ?></td>
                            <td><?php echo (int) $item->state; ?></td>
                            <td>
                                <?php if ($this->canDelete) : ?>
                                    <form action="<?php echo $action; ?>" method="post" onsubmit="<?php echo $confirm('COM_TTC_SPIELPLANUNG_CONFIRM_DELETE'); ?>">
                                        <input type="hidden" name="task" value="eintraege.remove">
                                        <input type="hidden" name="entry_id" value="<?php echo (int) $item->id; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><?php echo Text::_('COM_TTC_SPIELPLANUNG_DELETE'); ?></button>
                                        <?php echo HTMLHelper::_('form.token'); ?>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$this->items) : ?>
                        <tr><td colspan="7"><?php echo Text::_('COM_TTC_SPIELPLANUNG_NO_ENTRIES'); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm">
            <input type="hidden" name="task" value="">
            <input type="hidden" name="entries_player" value="<?php echo (int) $this->playerId; ?>">
            <input type="hidden" name="entries_sort" value="<?php echo $escape($this->sort); ?>">
            <input type="hidden" name="entries_direction" value="<?php echo $escape($this->direction); ?>">
            <?php echo $this->pagination->getListFooter(); ?>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
