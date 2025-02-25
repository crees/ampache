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

use Ampache\Repository\Model\Tag;
use Ampache\Repository\Model\Video;
use Ampache\Module\Util\ObjectTypeToClassNameMapper;
use Ampache\Module\Util\Ui;

/** @var Video $libitem */

$libitem = Video::create_from_id($libitem->id);
$libitem->format();
$video_type = ObjectTypeToClassNameMapper::reverseMap(get_class($libitem)); ?>
<div>
    <form method="post" id="edit_video_<?php echo $libitem->id; ?>" class="edit_dialog_content">
        <table class="tabledata">
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Title') ?></td>
                <td><input type="text" name="title" value="<?php echo scrub_out($libitem->title); ?>" autofocus /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Release Date') ?></td>
                <td><input type="text" name="release_date" value="<?php echo $libitem->f_release_date; ?>" /></td>
            </tr>
<?php
if ($video_type != 'video') {
    require Ui::find_template('show_partial_edit_' . $video_type . '_row.inc.php');
} ?>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Genres') ?></td>
                <td><input type="text" name="edit_tags" id="edit_tags" value="<?php echo Tag::get_display($libitem->tags); ?>" /></td>
            </tr>
        </table>
        <input type="hidden" name="id" value="<?php echo $libitem->id; ?>" />
        <input type="hidden" name="type" value="<?php echo $video_type; ?>_row" />
    </form>
</div>
