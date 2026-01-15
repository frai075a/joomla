<?php
/**
 * @version    CVS: 2.1.0
 * @package    Com_Qttr_rechner
 * @author     Doganer/Austen <fb@ttc-nordend.de>
 * @copyright  2022 Thorsten
 * @license    GNU General Public License Version 2 oder später; siehe LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Session\Session;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user       = Factory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_qttr_rechner') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'spielform.xml');
$canEdit    = $user->authorise('core.edit', 'com_qttr_rechner') && file_exists(JPATH_COMPONENT .  DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'spielform.xml');
$canCheckin = $user->authorise('core.manage', 'com_qttr_rechner');
$canChange  = $user->authorise('core.edit.state', 'com_qttr_rechner');
$canDelete  = $user->authorise('core.delete', 'com_qttr_rechner');

// Import CSS
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle('com_qttr_rechner.list');
echo "Erfasse hier die (potentiellen) Spiele, um die Auswirkungen auf deinen QTTR-Wert angezeigt zu bekommen";
?>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
	  name="adminForm" id="adminForm">
	
	<div class="table-responsive">
		<table class="table table-striped" id="spielList">
			<thead>
			<tr>
				
<!--					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_QTTR_RECHNER_SPIELE_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
-->
					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_QTTR_RECHNER_SPIELE_QTTR_WERT', 'a.qttr_wert', $listDirn, $listOrder); ?>
					</th>

					<th >
						<?php echo HTMLHelper::_('grid.sort', 'COM_QTTR_RECHNER_SPIELE_SIEG', 'a.sieg', $listDirn, $listOrder); ?>
					</th>

					<th >
						<?php echo HTMLHelper::_('grid.sort', 'COM_QTTR_RECHNER_SPIELE_LETZTESSPIEL', 'a.letztesspiel', $listDirn, $listOrder); ?>
					</th>

						<?php if ($canEdit || $canDelete): ?>
					<th class="center">
						<?php echo Text::_('COM_QTTR_RECHNER_SPIELE_ACTIONS'); ?>
					</th>
					<?php endif; ?>

			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_qttr_rechner'); ?>
				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_qttr_rechner')): ?>
				<?php $canEdit = Factory::getUser()->id == $item->created_by; ?>
				<?php endif; ?>

				<tr class="row<?php echo $i % 2; ?>">
<!--					
					<td>
						<?php echo $item->id; ?>
					</td>
-->
					<td>
						<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
<?php
/*						<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? JRoute::_('index.php?option=com_qttr_rechner&task=spiel.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
  						<?php if ($item->state == 1): ?>
 							<i class="icon-publish"></i>
  						<?php else: ?>
  							<i class="icon-unpublish"></i>
  						<?php endif; ?>
						</a>
*/
?>
					
						<?php echo $item->qttr_wert; ?>
					</td>
					<td>
						<?php echo $item->sieg; ?>
					</td>
					<td>					
						<?php echo $item->letztesspiel; ?>
					</td>

					<?php if ($canEdit || $canDelete): ?>
						<td class="center">
							<?php $canCheckin = Factory::getUser()->authorise('core.manage', 'com_qttr_rechner.' . $item->id) || $this->item->checked_out == Factory::getUser()->id; ?>

							<?php if($canEdit && $item->checked_out == 0): ?>
								<a href="<?php echo Route::_('index.php?option=com_qttr_rechner&task=spiel.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
							<?php endif; ?>
							<?php if ($canDelete): ?>
								<a href="<?php echo Route::_('index.php?option=com_qttr_rechner&task=spielform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
							<?php endif; ?>
						</td>
					<?php endif; ?>

				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_qttr_rechner&task=spielform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i>
			<?php echo Text::_('COM_QTTR_RECHNER_ADD_ITEM'); ?></a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value=""/>
	<input type="hidden" name="filter_order_Dir" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php
	if($canDelete) {
		$wa->addInlineScript("
			jQuery(document).ready(function () {
				jQuery('.delete-button').click(deleteItem);
			});

			function deleteItem() {

				if (!confirm(\"" . Text::_('COM_QTTR_RECHNER_DELETE_MESSAGE') . "\")) {
					return false;
				}
			}
		", [], [], ["jquery"]);
	}
?>