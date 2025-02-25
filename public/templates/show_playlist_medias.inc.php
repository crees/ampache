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
use Ampache\Repository\Model\Browse;
use Ampache\Repository\Model\Playlist;
use Ampache\Repository\Model\Search;
use Ampache\Repository\Model\User;
use Ampache\Module\Util\InterfaceImplementationChecker;
use Ampache\Module\Util\ObjectTypeToClassNameMapper;
use Ampache\Module\Util\Ui;

/** @var Browse $browse */
/** @var Playlist $playlist */
/** @var Search $search */
/** @var array $object_ids */

// playlists and searches come from the same 'playlist_media' browse
$playlist     = $playlist ?? $search;
$web_path     = AmpConfig::get('web_path');
$seconds      = $browse->duration;
$duration     = floor($seconds / 3600) . gmdate(":i:s", $seconds % 3600);
$show_ratings = User::is_registered() && (AmpConfig::get('ratings'));
$hide_genres  = AmpConfig::get('hide_genres');
$is_table     = $browse->is_grid_view();
//mashup and grid view need different css
$cel_cover = ($is_table) ? "cel_cover" : 'grid_cover';
$cel_time  = ($is_table) ? "cel_time" : 'grid_time';
$count     = 1; ?>
<?php if ($browse->is_show_header()) {
    require Ui::find_template('list_header.inc.php');
    echo '<span class="item-duration">' . '| ' . T_('Duration') . ': ' . $duration . '</span>';
} ?>
    <form method="post" id="reorder_playlist_<?php echo $playlist->id; ?>">
        <table id="reorder_playlist_table" class="tabledata striped-rows <?php echo $browse->get_css_class() ?>" data-objecttype="media" data-offset="<?php echo $browse->get_start() ?>">
            <thead>
            <tr class="th-top">
                <th class="cel_play essential"></th>
                <th class="<?php echo $cel_cover; ?> optional"><?php echo T_('Art') ?></th>
                <th class="cel_title essential persist"><?php echo T_('Title'); ?></th>
                <th class="cel_add essential"></th>
                <th class="<?php echo $cel_time; ?> optional"><?php echo T_('Time'); ?></th>
                <?php if ($show_ratings) { ?>
                    <th class="cel_ratings optional"><?php echo T_('Rating'); ?></th>
                    <?php } ?>
                <th class="cel_action essential"><?php echo T_('Action'); ?></th>
                <th class="cel_drag essential"></th>
            </tr>
            </thead>
            <tbody id="sortableplaylist_<?php echo $playlist->id; ?>">
            <?php foreach ($object_ids as $object) {
                if (!is_array($object)) {
                    $object = (array) $object;
                }
                $object_type = $object['object_type'];
                if (InterfaceImplementationChecker::is_library_item($object_type)) {
                    /** @var Ampache\Repository\Model\playable_item $libitem */
                    $class_name = ObjectTypeToClassNameMapper::map($object_type);
                    $libitem    = new $class_name($object['object_id']);
                    $libitem->format();
                    $playlist_track = (int)($object['track'] ?? $count); ?>
                    <tr id="track_<?php echo($object['track_id'] ?? $count) ?>">
                        <?php require Ui::find_template('show_playlist_media_row.inc.php'); ?>
                    </tr>
                    <?php
                }
                $count++;
            } ?>
            </tbody>
            <tfoot>
            <tr class="th-bottom">
                <th class="cel_play"><?php echo T_('Play'); ?></th>
                <th class="<?php echo $cel_cover; ?>"><?php echo T_('Art') ?></th>
                <th class="cel_title"><?php echo T_('Title'); ?></th>
                <th class="cel_add"></th>
                <th class="<?php echo $cel_time; ?>"><?php echo T_('Time'); ?></th>
                <?php if ($show_ratings) { ?>
                    <th class="cel_ratings optional"><?php echo T_('Rating'); ?></th>
                    <?php } ?>
                <th class="cel_action"><?php echo T_('Action'); ?></th>
                <th class="cel_drag"></th>
            </tr>
            </tfoot>
        </table>
    </form>
<?php show_table_render($argument); ?>
<?php if ($browse->is_show_header()) {
    require Ui::find_template('list_header.inc.php');
    echo '<span class="item-duration">' . '| ' . T_('Duration') . ': ' . $duration . '</span>';
} ?>