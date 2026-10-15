<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
$this->getDocument()->getWebAssetManager()->useScript('multiselect');
?>
<p><a href="<?php echo Route::_('index.php?option=com_ttc_hallenbuchung&view=hallenfussballteilnehmer'); ?>"><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TITLE_HALLENFUSSBALLTEILNEHMER'); ?></a> | <?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_TITLE'); ?></p>
<form id="adminForm" name="adminForm" method="post" action="<?php echo Route::_('index.php?option=com_ttc_hallenbuchung&view=tttrainings'); ?>">
<div class="input-group mb-3">
<input type="search" class="form-control" name="filter_search" value="<?php echo $this->escape($this->state->get('filter.search', '')); ?>" aria-label="<?php echo Text::_('JSEARCH_FILTER'); ?>">
<button class="btn btn-secondary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
</div>
<table class="table table-striped">
<thead><tr>
<th><?php echo HTMLHelper::_('grid.checkall'); ?></th>
<th><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_DATE'); ?></th>
<th><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_SLOT'); ?></th>
<th><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_PAIR'); ?></th>
<th><?php echo Text::_('JSTATUS'); ?></th>
</tr></thead><tbody>
<?php foreach ($this->items as $i => $item) : ?>
<tr>
<td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
<td><a href="<?php echo Route::_('index.php?option=com_ttc_hallenbuchung&task=tttraining.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape((new \DateTimeImmutable($item->datum))->format('d.m.Y')); ?></a></td>
<td><?php echo $this->escape(in_array((string) $item->slot, ['1', '2'], true) ? Text::_('COM_TTC_HALLENBUCHUNG_TT_SLOT_' . $item->slot) : $item->slot); ?></td>
<td><?php echo $this->escape($item->paarung); ?></td>
<td><?php echo Text::_([1 => 'JPUBLISHED', 0 => 'JUNPUBLISHED', 2 => 'JARCHIVED', -2 => 'JTRASHED'][(int) $item->state] ?? 'JUNDEFINED'); ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php echo $this->pagination->getListFooter(); ?>
<input type="hidden" name="task" value=""><input type="hidden" name="boxchecked" value="0">
<?php echo HTMLHelper::_('form.token'); ?>
</form>
