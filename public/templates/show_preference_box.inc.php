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

/* I'm cheating a little here, check to see if we want to show the
 * Apply to All button on this page
 */

use Ampache\Module\Authorization\Access;
use Ampache\Module\Util\UiInterface;

/** @var UiInterface $ui */
/** @var array<string, mixed> $preferences */

$is_system = ($preferences['title'] === 'System');
$is_admin  = (Access::check('interface', 100) && (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'admin')) ?>
<h4><?php echo T_($preferences['title']); ?></h4>
<table class="tabledata striped-rows">
<colgroup>
  <col id="col_preference" />
  <col id="col_value" />
    <?php if ($is_admin) {
        if (!$is_system) { ?>
  <col id="col_applytoall" />
  <col id="col_level" />
    <?php }
        } ?>
</colgroup>
<thead>
    <tr class="th-top">
        <th class="cel_preference"><?php echo T_('Preference'); ?></th>
        <th class="cel_value"><?php echo T_('Value'); ?></th>
        <?php if ($is_admin) {
            if (!$is_system) { ?>
        <th class="cel_applytoall"><?php echo T_('Apply to All'); ?></th>
        <th class="cel_level"><?php echo T_('Access Level'); ?></th>
        <?php }
            } ?>
    </tr>
</thead>
<tbody>
    <?php
                $lastsubcat = '';
foreach ($preferences['prefs'] as $pref) {
    if ($pref['subcategory'] != $lastsubcat) {
        $lastsubcat = $pref['subcategory'];
        $fsubcat    = $lastsubcat;
        if (!empty($fsubcat)) { ?>
                <tr><td colspan="4"><h5><?php echo ucwords(T_($fsubcat)) ?></h5></td></tr>
                <?php
        }
    } ?>
        <tr>
            <td class="cel_preference"><?php echo T_($pref['description']); ?></td>
            <td class="cel_value">
                <?php echo $ui->createPreferenceInput($pref['name'], $pref['value']); ?>
            </td>
            <?php if ($is_admin) {
                if (!$is_system) { ?>
                <td class="cel_applytoall"><input type="checkbox" name="check_<?php echo $pref['name']; ?>" value="1" /></td>
                <td class="cel_level">
                    <?php $name         = 'on_' . (string)$pref['level'];
                    $on_5               = '';
                    $on_25              = '';
                    $on_50              = '';
                    $on_75              = '';
                    $on_100             = '';
                    switch ($name) {
                        case 'on_5':
                            $on_5 = 'selected="selected"';
                            break;
                        case 'on_25':
                            $on_25 = 'selected="selected"';
                            break;
                        case 'on_50':
                            $on_50 = 'selected="selected"';
                            break;
                        case 'on_75':
                            $on_75 = 'selected="selected"';
                            break;
                        case 'on_100':
                            $on_100 = 'selected="selected"';
                            break;
                    } ?>
                    <select name="level_<?php echo $pref['name']; ?>">
                        <option value="5" <?php echo $on_5; ?>><?php echo T_('Guest'); ?></option>
                        <option value="25" <?php echo $on_25; ?>><?php echo T_('User'); ?></option>
                        <option value="50" <?php echo $on_50; ?>><?php echo T_('Content Manager'); ?></option>
                        <option value="75" <?php echo $on_75; ?>><?php echo T_('Catalog Manager'); ?></option>
                        <option value="100" <?php echo $on_100; ?>><?php echo T_('Admin'); ?></option>
                    </select>
                    <?php unset(${$name}); ?>
                </td>
            <?php }
                } ?>
        </tr>
    <?php
} // End foreach ($preferences['prefs'] as $pref)?>
</tbody>
<tfoot>
    <tr class="th-bottom">
        <th class="cel_preference"><?php echo T_('Preference'); ?></th>
        <th class="cel_value"><?php echo T_('Value'); ?></th>
        <?php if ($is_admin) {
            if (!$is_system) { ?>
        <th class="cel_applytoall"><?php echo T_('Apply to All'); ?></th>
        <th class="cel_level"><?php echo T_('Access Level'); ?></th>
        <?php } ?>
        <?php
        } ?>
    </tr>
</tfoot>
</table>
