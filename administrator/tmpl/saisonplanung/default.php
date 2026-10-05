<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();
$filterCategoryId = (int) $this->state->get('filter.category_id');
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?php echo Text::_('COM_TTC_SPIELPLANUNG_TITLE_SAISONPLANUNG'); ?></h3>
            </div>
            <div class="card-body">
                <form action="<?php echo Route::_('index.php?option=com_ttc_spielplanung&view=saisonplanung'); ?>" method="get" name="saisonplanungFilterForm" id="saisonplanungFilterForm" class="form-inline mb-3">
                    <input type="hidden" name="option" value="com_ttc_spielplanung" />
                    <input type="hidden" name="view" value="saisonplanung" />
                    <div class="form-group">
                        <label for="filter_category_id" class="me-2"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_CATEGORY'); ?></label>
                        <select name="filter_category_id" id="filter_category_id" class="form-select form-select-sm" style="width: auto; display: inline-block;" onchange="this.form.submit();">
                            <option value=""><?php echo Text::_('COM_TTC_SPIELPLANUNG_FILTER_ALL_CATEGORIES'); ?></option>
                            <?php foreach ($this->categories as $catId => $cat) : ?>
                                <option value="<?php echo (int) $catId; ?>" <?php echo $filterCategoryId == $catId ? 'selected="selected"' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 180px;"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_CATEGORY'); ?></th>
                            <th style="width: 150px;"><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_SPIELDATUM'); ?></th>
                            <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_GEGNER'); ?></th>
                            <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_SPORTHALLE'); ?></th>
                            <th><?php echo Text::_('COM_TTC_SPIELPLANUNG_FIELD_CONFIRMED_PLAYERS'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($this->items) : ?>
                            <?php foreach ($this->items as $item) : ?>
                                <?php $players = $this->confirmedPlayers[(int) $item->game_id] ?? array(); ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo HTMLHelper::_('date', $item->spieldatum . ' ' . $item->uhrzeit, 'd.m.Y H:i'); ?></td>
                                    <td><?php echo htmlspecialchars($item->gegner, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($item->sporthalle, ENT_QUOTES, 'UTF-8'); ?></td>
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
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php echo $this->pagination->getListFooter(); ?>
            </div>
        </div>
    </div>
</div>
