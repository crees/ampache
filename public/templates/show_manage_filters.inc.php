<?php
/* vim:set softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2022 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

use Ampache\Config\AmpConfig;
use Ampache\Repository\Model\Catalog;
use Ampache\Module\Util\Ui;

/** @var Ampache\Repository\Model\Browse $browse */
/** @var array $object_ids */

$web_path = AmpConfig::get('web_path'); ?>
<?php Ui::show_box_top(T_('Show Catalog Filters'), 'box box_manage_filter'); ?>
<div id="information_actions">
    <ul style="float: left;">
        <li>
            <a class="option-list" href="<?php echo $web_path; ?>/admin/filter.php?action=show_add_filter"><?php echo T_('Add Catalog Filter'); ?></a>
        </li>
    </ul>
</div>
<table class="tabledata striped-rows" data-objecttype="filter">
    <thead>
        <tr class="th-top">
            <th class="cel_name essential persist"><?php echo T_('Filter name'); ?></th>
            <th class="cel_num_users essential"><?php echo T_('Users'); ?></th>
            <th class="cel_num_catalogs essential"><?php echo T_('Catalogs'); ?></th>
            <th class="cel_action cel_action_text essential"><?php echo T_('Actions'); ?></th>
        </tr>
    </thead>
    <tbody>
<?php $filters = Catalog::get_catalog_filters();
foreach ($filters as $filter) {
    $num_users    = Catalog::filter_user_count($filter['id']);
    $num_catalogs = Catalog::filter_catalog_count($filter['id']);
    //debug_event(self::class, "Values:  fname:$filter_name, fid:$filter_id, nu:$num_users, nc:num_catalogs", 5);?>
        <tr id="<?php echo $filter['name']; ?>">
            <?php require Ui::find_template('show_filter_row.inc.php'); ?>
        </tr>
<?php
}
?>
    </tbody>
    <tfoot>
        <tr class="th-bottom">
            <th class="cel_name"><?php echo T_('Filter Name'); ?></th>
            <th class="cel_num_users"><?php echo T_('Number of Users'); ?></th>
            <th class="cel_num_catalogs"><?php echo T_('Number of Catalogs'); ?></th>
            <th class="cel_action cel_action_text"><?php echo T_('Actions'); ?></th>
        </tr>
    </tfoot>
</table>
<?php //require Ui::find_template('list_header.inc.php');?>
