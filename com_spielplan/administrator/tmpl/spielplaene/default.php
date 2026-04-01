<?php
/**
 * @version    CVS: 1.0.6
 * @package    Com_Spielplan
 * @author     Thorsten Austen <thorsten.austen@gmail.com>
 * @copyright  2024 Thorsten Austen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;


use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

// Import CSS
$wa =  $this->document->getWebAssetManager();
$wa->useStyle('com_spielplan.admin')
    ->useScript('com_spielplan.admin');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_spielplan');



if (!empty($saveOrder))
{
	$saveOrderingUrl = 'index.php?option=com_spielplan&task=spielplaene.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>

<form action="<?php echo Route::_('index.php?option=com_spielplan&view=spielplaene'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
			<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
				<table class="table table-striped" id="spielplanList">
					<thead>
					<tr>
						<th class="w-1 text-center">
							<input type="checkbox" autocomplete="off" class="form-check-input" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						
						
						
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_SPIELPLAN_SPIELPLAENE_MANNSCHAFT', 'a.mannschaft', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_SPIELPLAN_SPIELPLAENE_DATUM', 'a.datum', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_SPIELPLAN_SPIELPLAENE_UHRZEIT', 'a.uhrzeit', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_SPIELPLAN_SPIELPLAENE_HEIMMANNSCHAFT', 'a.heimmannschaft', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_SPIELPLAN_SPIELPLAENE_H_NUMMER', 'a.h_nummer', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_SPIELPLAN_SPIELPLAENE_AUSWAERTSMANNSCHAFT', 'a.auswaertsmannschaft', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_SPIELPLAN_SPIELPLAENE_A_NUMMER', 'a.a_nummer', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_SPIELPLAN_SPIELPLAENE_ORT', 'a.ort', $listDirn, $listOrder); ?>
						</th>
						
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
					</tfoot>
					<tbody <?php if (!empty($saveOrder)) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" <?php endif; ?>>
					<?php foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'a.ordering');
						$canCreate  = $user->authorise('core.create', 'com_spielplan');
						$canEdit    = $user->authorise('core.edit', 'com_spielplan');
						$canCheckin = $user->authorise('core.manage', 'com_spielplan');
						$canChange  = $user->authorise('core.edit.state', 'com_spielplan');
						?>
						<tr class="row<?php echo $i % 2; ?>" data-draggable-group='1' data-transition>
							<td class="text-center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							
							
							
							<td>
								<?php echo $item->mannschaft; ?>
							</td>
							<td>
								<?php echo Factory::getDate($item->datum)->format(Text::_('d.m.Y')); ?>
							</td>
							<td>
								<?php echo Factory::getDate($item->uhrzeit)->format(Text::_('H:i')); ?>
							</td>
							<td>
								<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'spielplaene.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo Route::_('index.php?option=com_spielplan&task=spielplan.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->heimmannschaft); ?>
									</a>
								<?php else : ?>
												<?php echo $this->escape($item->heimmannschaft); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php echo $item->h_nummer; ?>
							</td>
							<td>
								<?php echo $item->auswaertsmannschaft; ?>
							</td>
							<td>
								<?php echo $item->a_nummer; ?>
							</td>
							<td>
								<?php echo $item->ort; ?>
							</td>
							

						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
<?php if ($user->authorise('core.create', 'com_spielplan')) : ?>
<form action="<?php echo Route::_('index.php?option=com_spielplan&task=spielplaene.importSpielplan'); ?>"
      method="post" name="importSpielplanForm" id="importSpielplanForm"
      enctype="multipart/form-data"
      style="margin-top: 1rem;">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div>
            <label for="spielplan_csv" class="form-label mb-1">
                <?php echo Text::_('COM_SPIELPLAN_IMPORT_LABEL_FILE'); ?>
            </label>
            <input type="file"
                   id="spielplan_csv"
                   name="spielplan_csv"
                   accept=".csv"
                   class="form-control"
                   style="max-width: 360px;"
                   required />
        </div>
        <div style="padding-top: 1.5rem;">
            <button type="submit" class="btn btn-success"
                    onclick="return confirm('<?php echo Text::_('COM_SPIELPLAN_IMPORT_CONFIRM'); ?>');">
                <span class="icon-upload" aria-hidden="true"></span>
                <?php echo Text::_('COM_SPIELPLAN_IMPORT_BUTTON'); ?>
            </button>
        </div>
    </div>
    <div class="form-text text-muted mt-1">
        <?php echo Text::_('COM_SPIELPLAN_IMPORT_HINT'); ?>
    </div>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php endif; ?>

<?php if ($user->authorise('core.delete', 'com_spielplan')) : ?>
<form action="<?php echo Route::_('index.php?option=com_spielplan&task=spielplaene.deleteSpielplan'); ?>"
      method="post" name="deleteSpielplanForm" id="deleteSpielplanForm"
      style="margin-top: 1rem;">
    <button type="submit" class="btn btn-danger"
            onclick="return confirm('<?php echo Text::_('COM_SPIELPLAN_DELETE_SPIELPLAN_CONFIRM'); ?>');">
        <span class="icon-trash" aria-hidden="true"></span>
        <?php echo Text::_('COM_SPIELPLAN_DELETE_SPIELPLAN_BUTTON'); ?>
    </button>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php endif; ?>