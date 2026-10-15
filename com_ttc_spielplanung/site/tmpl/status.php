<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;

$currentStatus = $game['status'] === null ? 'neutral' : (string) (int) $game['status'];
?>
<fieldset class="com-ttc-spielplanung-status-switch">
    <legend class="visually-hidden"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_STATUS'); ?></legend>
    <?php foreach (['0' => 'NO', 'neutral' => 'NEUTRAL', '1' => 'YES'] as $value => $label) :
        $id = 'status_' . (int) $game['game_id'] . '_' . $value;
    ?>
        <input type="radio" id="<?php echo $id; ?>" name="status_<?php echo (int) $game['game_id']; ?>" value="<?php echo $value; ?>" <?php echo $currentStatus === (string) $value ? 'checked' : ''; ?>>
        <label for="<?php echo $id; ?>"><?php echo Text::_('COM_TTC_SPIELPLANUNG_STATUS_' . $label); ?></label>
    <?php endforeach; ?>
</fieldset>
