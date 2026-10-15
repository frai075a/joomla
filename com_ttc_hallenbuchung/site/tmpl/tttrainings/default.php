<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Helper\TttrainingHelper;
$user = Factory::getApplication()->getIdentity();
?>
<h1><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_TITLE'); ?></h1>
<p><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_INTRO'); ?></p>
<?php if ($user->id && $user->authorise('core.create', 'com_ttc_hallenbuchung')) : ?>
<p><a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_ttc_hallenbuchung&view=tttrainingform&id=0'); ?>"><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_BOOK'); ?></a></p>
<?php endif; ?>
<form method="get" action="<?php echo Route::_('index.php'); ?>" class="mb-3">
<input type="hidden" name="option" value="com_ttc_hallenbuchung">
<input type="hidden" name="view" value="tttrainings">
<input type="hidden" name="Itemid" value="<?php echo Factory::getApplication()->getInput()->getInt('Itemid'); ?>">
<div class="input-group">
<input type="search" class="form-control" name="filter_search" value="<?php echo $this->escape($this->state->get('filter.search', '')); ?>" aria-label="<?php echo Text::_('JSEARCH_FILTER'); ?>">
<button type="submit" class="btn btn-secondary"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
</div>
</form>
<div class="table-responsive"><table class="table table-striped">
<thead><tr>
<th><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_DATE'); ?></th>
<th><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_SLOT'); ?></th>
<th><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_PAIR'); ?></th>
<th><?php echo Text::_('COM_TTC_HALLENBUCHUNG_HALLENFUSSBALLTEILNEHMER_ACTIONS'); ?></th>
</tr></thead><tbody>
<?php foreach ($this->items as $item) : ?>
<tr>
<td><?php echo $this->escape((new \DateTimeImmutable($item->datum))->format('d.m.Y')); ?></td>
<td><?php echo $this->escape(in_array((string) $item->slot, ['1', '2'], true) ? Text::_('COM_TTC_HALLENBUCHUNG_TT_SLOT_' . $item->slot) : $item->slot); ?></td>
<td><?php echo $this->escape($item->paarung); ?></td>
<td>
<?php if (TttrainingHelper::canEdit($item, $user)) : ?>
<a class="btn btn-sm btn-secondary" href="<?php echo Route::_('index.php?option=com_ttc_hallenbuchung&view=tttrainingform&id=' . (int) $item->id); ?>"><?php echo Text::_('JGLOBAL_EDIT'); ?></a>
<?php endif; ?>
<?php if ($user->id && $user->authorise('core.delete', 'com_ttc_hallenbuchung')) : ?>
<form method="post" class="d-inline" action="<?php echo Route::_('index.php?option=com_ttc_hallenbuchung'); ?>">
<input type="hidden" name="id" value="<?php echo (int) $item->id; ?>">
<button class="btn btn-sm btn-danger" type="submit" name="task" value="tttrainingform.remove"><?php echo Text::_('JACTION_DELETE'); ?></button>
<?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php endif; ?>
</td></tr>
<?php endforeach; ?>
<?php if (!$this->items) : ?><tr><td colspan="4"><?php echo Text::_('COM_TTC_HALLENBUCHUNG_TT_EMPTY'); ?></td></tr><?php endif; ?>
</tbody></table></div>
<?php echo $this->pagination->getPagesLinks(); ?>
