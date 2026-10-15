<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$escape = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$date = '31.12.' . (int) $this->season['year'];
?>
<div class="card">
    <div class="card-header"><h3><?php echo Text::_('COM_TTC_SPIELPLANUNG_TITLE_SAISONINITIALISIEREN'); ?></h3></div>
    <div class="card-body">
        <?php if ($this->season['exists']) : ?>
            <div class="alert alert-success"><?php echo $escape(Text::sprintf('COM_TTC_SPIELPLANUNG_SEASON_EXISTS_DATE', $date)); ?></div>
        <?php else : ?>
            <p><?php echo $escape(Text::sprintf('COM_TTC_SPIELPLANUNG_SEASON_MISSING_DATE', $date)); ?></p>
            <?php if ($this->canCreate) : ?>
                <form action="<?php echo Route::_('index.php?option=com_ttc_spielplanung&view=saisoninitialisieren'); ?>" method="post">
                    <input type="hidden" name="task" value="saisoninitialisieren.initialize">
                    <button type="submit" class="btn btn-primary"><?php echo $escape(Text::sprintf('COM_TTC_SPIELPLANUNG_SEASON_BUTTON', (int) $this->season['year'], (int) $this->season['nextYear'])); ?></button>
                    <?php echo HTMLHelper::_('form.token'); ?>
                </form>
            <?php else : ?>
                <p><?php echo Text::_('COM_TTC_SPIELPLANUNG_SEASON_DENIED'); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
