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
use Ampache\Repository\Model\Art;
use Ampache\Repository\Model\Catalog;
use Ampache\Repository\Model\Rating;
use Ampache\Repository\Model\User;
use Ampache\Repository\Model\Userflag;
use Ampache\Module\Authorization\Access;
use Ampache\Module\Api\Ajax;
use Ampache\Module\Playback\Stream_Playlist;
use Ampache\Repository\Model\Browse;
use Ampache\Module\Util\Ui;

/* @var string $object_type */
/* @var Ampache\Repository\Model\TVShow $tvshow */
/* @var array $object_ids */

$web_path = AmpConfig::get('web_path');
$browse   = new Browse();
$browse->set_type($object_type);

Ui::show_box_top($tvshow->f_name, 'info-box'); ?>
<div class="item_right_info">
    <?php
    Art::display('tvshow', $tvshow->id, $tvshow->f_name, 6); ?>
    <?php if ($tvshow->summary) { ?>
    <div id="item_summary">
        <?php echo $tvshow->summary; ?>
    </div>
    <?php } ?>
</div>
<?php if (User::is_registered()) { ?>
    <?php
    if (AmpConfig::get('ratings')) { ?>
    <span id="rating_<?php echo (int) ($tvshow->id); ?>_tvshow">
        <?php echo Rating::show($tvshow->id, 'tvshow'); ?>
    </span>
    <span id="userflag_<?php echo $tvshow->id; ?>_tvshow">
        <?php echo Userflag::show($tvshow->id, 'tvshow'); ?>
    </span>
    <?php } ?>
<?php } ?>
<div id="information_actions">
    <h3><?php echo T_('Actions'); ?>:</h3>
    <ul>
        <?php if (AmpConfig::get('directplay')) { ?>
        <li>
            <?php echo Ajax::button_with_text('?page=stream&action=directplay&object_type=tvshow&object_id=' . $tvshow->id, 'play', T_('Play All'), 'directplay_full_' . $tvshow->id); ?>
        </li>
        <?php } ?>
        <?php if (Stream_Playlist::check_autoplay_next()) { ?>
        <li>
            <?php echo Ajax::button_with_text('?page=stream&action=directplay&object_type=tvshow&object_id=' . $tvshow->id . '&playnext=true', 'play_next', T_('Play All Next'), 'nextplay_tvshow_' . $tvshow->id); ?>
        </li>
        <?php } ?>
        <?php if (Stream_Playlist::check_autoplay_append()) { ?>
        <li>
            <?php echo Ajax::button_with_text('?page=stream&action=directplay&object_type=tvshow&object_id=' . $tvshow->id . '&append=true', 'play_add', T_('Play All Last'), 'addplay_tvshow_' . $tvshow->id); ?>
        </li>
        <?php } ?>
        <?php if (Access::check('interface', 50)) { ?>
        <li>
            <a id="<?php echo 'edit_tvshow_' . $tvshow->id ?>" onclick="showEditDialog('tvshow_row', '<?php echo $tvshow->id ?>', '<?php echo 'edit_tvshow_' . $tvshow->id ?>', '<?php echo addslashes(T_('TV Show Edit')) ?>', '')">
                <?php echo Ui::get_icon('edit', T_('Edit')); ?>
                <?php echo T_('Edit TV Show'); ?>
            </a>
        </li>
        <?php } ?>
        <?php if (Catalog::can_remove($tvshow)) { ?>
        <li>
            <a id="<?php echo 'delete_tvshow_' . $tvshow->id ?>" href="<?php echo $web_path; ?>/tvshows.php?action=delete&tvshow_id=<?php echo $tvshow->id; ?>">
                <?php echo Ui::get_icon('delete', T_('Delete')); ?>
                <?php echo T_('Delete'); ?>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php Ui::show_box_bottom(); ?>
<div class="tabs_wrapper">
    <div id="tabs_container">
        <ul id="tabs">
            <li class="tab_active"><a href="#seasons"><?php echo T_('Seasons'); ?></a></li>
            <!-- Needed to avoid the 'only one' bug -->
            <li></li>
        </ul>
    </div>
    <div id="tabs_content">
        <div id="seasons" class="tab_content" style="display: block;">
<?php $browse->show_objects($object_ids, true);
$browse->store(); ?>
        </div>
    </div>
</div>
