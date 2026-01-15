<?php
/**
 * @version    CVS: 2.1
 * @package    Com_Ttc_hallenfussball
 * @author     Thorsten Austen <fb@ttc-nordend.de>
 * @copyright  Copyright (C) 2013-2019. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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
use \Joomla\CMS\User\UserFactoryInterface;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
//HTMLHelper::_('formbehavior.chosen', 'select');

$user       = Factory::getApplication()->getIdentity();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_ttc_hallenfussball') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'hallenfussballteilnahmeform.xml');
$canEdit    = $user->authorise('core.edit', 'com_ttc_hallenfussball') && file_exists(JPATH_COMPONENT .  DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'hallenfussballteilnahmeform.xml');
$canCheckin = $user->authorise('core.manage', 'com_ttc_hallenfussball');
$canChange  = $user->authorise('core.edit.state', 'com_ttc_hallenfussball');
$canDelete  = $user->authorise('core.delete', 'com_ttc_hallenfussball');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_ttc_hallenfussball.list');
?>

<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
<?php endif;?>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
	  name="adminForm" id="adminForm">
	<?php if(!empty($this->filterForm)) { echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); } ?>
	<div class="table-responsive">
		<table class="table table-striped" id="hallenfussballteilnahmeList">
			<thead>
			<tr>
				
					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_TTC_HALLENFUSSBALL_HALLENFUSSBALLTEILNEHMER_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_TTC_HALLENFUSSBALL_HALLENFUSSBALLTEILNEHMER_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_TTC_HALLENFUSSBALL_HALLENFUSSBALLTEILNEHMER_DATUM', 'a.datum', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_TTC_HALLENFUSSBALL_HALLENFUSSBALLTEILNEHMER_TEILNEHMER', 'a.teilnehmer', $listDirn, $listOrder); ?>
					</th>

					<th class=''>
						<?php echo HTMLHelper::_('grid.sort',  'COM_TTC_HALLENFUSSBALL_HALLENFUSSBALLTEILNEHMER_ZUSAGE', 'a.zusage', $listDirn, $listOrder); ?>
					</th>

						<?php if ($canEdit || $canDelete): ?>
					<th class="center">
						<?php echo Text::_('COM_TTC_HALLENFUSSBALL_HALLENFUSSBALLTEILNEHMER_ACTIONS'); ?>
					</th>
					<?php endif; ?>

			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<div class="pagination">
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_ttc_hallenfussball'); ?>
				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_ttc_hallenfussball')): ?>
				<?php $canEdit = Factory::getApplication()->getIdentity()->id == $item->created_by; ?>
				<?php endif; ?>

				<tr class="row<?php echo $i % 2; ?>">
					
					<td>
						<?php echo $item->id; ?>
					</td>
					<td>
								<?php $container = \Joomla\CMS\Factory::getContainer();
								$userFactory = $container->get(UserFactoryInterface::class);?>
								<?php $user = $userFactory->loadUserById($item->created_by); ?>
								<?php echo $user->name; ?>
					</td>
					<td>
						<?php echo $item->datum; ?>
					</td>
					<td>
						<?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_ttc_hallenfussball.' . $item->id) || $item->checked_out == Factory::getApplication()->getIdentity()->id; ?>
						<?php if($canCheckin && $item->checked_out > 0) : ?>
							<a href="<?php echo Route::_('index.php?option=com_ttc_hallenfussball&task=hallenfussballteilnahme.checkin&id=' . $item->id .'&'. Session::getFormToken() .'=1'); ?>">
							<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'hallenfussballteilnahme.', false); ?></a>
						<?php endif; ?>
						<a href="<?php echo Route::_('index.php?option=com_ttc_hallenfussball&view=hallenfussballteilnahme&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->teilnehmer); ?></a>
					</td>
					<td>
						<?php echo $item->zusage; ?>
					</td>
					<?php if ($canEdit || $canDelete): ?>
						<td class="center">
							<?php if ($canEdit): ?>
								<a href="<?php echo Route::_('index.php?option=com_ttc_hallenfussball&task=hallenfussballteilnahme.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
							<?php endif; ?>
							<?php if ($canDelete): ?>
								<a href="<?php echo Route::_('index.php?option=com_ttc_hallenfussball&task=hallenfussballteilnahmeform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
							<?php endif; ?>
						</td>
					<?php endif; ?>

				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_ttc_hallenfussball&task=hallenfussballteilnahmeform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i>
			<?php echo Text::_('COM_TTC_HALLENFUSSBALL_ADD_ITEM'); ?></a>
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

				if (!confirm(\"" . Text::_('COM_TTC_HALLENFUSSBALL_DELETE_MESSAGE') . "\")) {
					return false;
				}
			}
		", [], [], ["jquery"]);
	}
?>