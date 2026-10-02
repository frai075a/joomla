<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$user = Factory::getApplication()->getIdentity();
?>

<style>
    .com-ttc-spielplanung-status-switch {
        display: inline-grid;
        grid-template-columns: repeat(3, auto);
        position: relative;
        padding: 0;
        margin: 0;
        border: 1px solid #6c757d;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .com-ttc-spielplanung-status-switch input {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
    }
    .com-ttc-spielplanung-status-switch label {
        padding: 0.45rem 0.65rem;
        cursor: pointer;
        margin: 0;
        background: #fff;
        color: #212529;
    }
    .com-ttc-spielplanung-status-switch input:checked + label {
        background: #495057;
        color: #fff;
        font-weight: 700;
    }
    .com-ttc-spielplanung-status-switch input[value="0"]:checked + label { background: #b02a37; }
    .com-ttc-spielplanung-status-switch input[value="1"]:checked + label { background: #146c43; }
    .com-ttc-spielplanung-status-switch input:focus-visible + label { outline: 3px solid #0d6efd; outline-offset: -3px; }
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
    <h1><?php echo Text::_('COM_TTC_SPIELPLANUNG_TITLE_SAISONPLANUNGMF'); ?></h1>

    <form action="<?php echo Route::_('index.php?option=com_ttc_spielplanung&view=saisonplanungmf'); ?>" method="get" name="saisonplanungFilterForm" id="saisonplanungFilterForm" class="com-ttc-spielplanung-filter-box">
        <input type="hidden" name="option" value="com_ttc_spielplanung" />
        <input type="hidden" name="view" value="saisonplanungmf" />
        <input type="hidden" name="only_future_submitted" value="1" />
        <input type="hidden" name="Itemid" value="<?php echo (int) $this->itemId; ?>" />
        <div class="mb-3">
            <label for="player_id" class="form-label"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_PLAYER'); ?></label>
            <select name="player_id" id="player_id" class="form-select" onchange="this.form.submit();">
                <?php foreach ($this->players as $player) : ?>
                    <option value="<?php echo (int) $player['id']; ?>" <?php echo (int) $player['id'] === $this->playerId ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($player['name'] . ' (' . $player['team_title'] . ')', ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
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
        <form action="<?php echo Route::_('index.php?option=com_ttc_spielplanung&task=saisonplanungmf.saveGame'); ?>" method="post" id="saisonplanungmfForm">
        <input type="hidden" name="player_id" value="<?php echo (int) $this->playerId; ?>" />
        <input type="hidden" name="only_future" value="<?php echo (int) $this->onlyFuture; ?>" />
        <input type="hidden" name="vorrunde" value="<?php echo (int) $this->vorrundeOnly; ?>" />
        <input type="hidden" name="Itemid" value="<?php echo (int) $this->itemId; ?>" />
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_SPIELDATUM'); ?></th>
                        <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_GEGNER'); ?></th>
                        <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_HOME_AWAY'); ?></th>
                        <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_STATUS'); ?></th>
                        <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_CONFIRMED_PLAYERS'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->games as $gameId => $game) : ?>
                        <?php $players = $this->confirmedPlayers[$gameId] ?? array(); ?>
                        <tr>
                            <td><?php echo HTMLHelper::_('date', $game['spieldatum'] . ' ' . $game['uhrzeit'], 'd.m.Y H:i'); ?></td>
                            <td><?php echo htmlspecialchars($game['gegner'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo str_starts_with((string) ($game['sporthalle'] ?? ''), 'Außenstelle der Beruflichen Schulen Berta Jourdan') ? 'H' : 'A'; ?></td>
                            <td>
                                <?php require __DIR__ . '/../status.php'; ?>
                                <input type="hidden" name="game_ids[]" value="<?php echo (int) $game['game_id']; ?>" />
                            </td>
                            <td>
                                <?php if ($players) : ?>
                                    <ol class="mb-0 ps-3">
                                        <?php foreach ($players as $playerName) : ?>
                                            <li><?php echo htmlspecialchars($playerName, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                <?php else : ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button type="submit" name="task" value="saisonplanungmf.saveGame" class="btn btn-primary"><?php echo Text::_('COM_TTC_SPIELPLANUNG_SAVE'); ?></button>
        <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    <?php else : ?>
        <div class="alert alert-info">
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php endif; ?>
</div>


