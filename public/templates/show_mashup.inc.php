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
use Ampache\Module\Statistics\Stats;
use Ampache\Module\System\Core;
use Ampache\Repository\Model\Browse;
use Ampache\Module\Util\Ui;

/** @var string $object_type */

$threshold      = AmpConfig::get('stats_threshold', 7);
$limit          = (int)AmpConfig::get('popular_threshold', 10);
$catalog_filter = AmpConfig::get('catalog_filter');
$user_id        = ($catalog_filter && !empty(Core::get_global('user')))
    ? Core::get_global('user')->id
    : null;

require_once Ui::find_template('show_form_mashup.inc.php');
Ui::show_box_top(T_('Trending'));
$object_ids = Stats::get_top($object_type, $limit, $threshold);
$browse     = new Browse();
$browse->set_type($object_type);
$browse->set_show_header(false);
$browse->set_grid_view(false, false);
$browse->set_mashup(true);
$browse->show_objects($object_ids);
Ui::show_box_bottom();
Ui::show_box_top(T_('Recent'));
$object_ids = Stats::get_recent($object_type, $limit);
$browse     = new Browse();
$browse->set_type($object_type);
$browse->set_show_header(false);
$browse->set_grid_view(false, false);
$browse->set_mashup(true);
$browse->show_objects($object_ids);
Ui::show_box_bottom();
echo "<a href=\"" . AmpConfig::get('web_path') . "/stats.php?action=newest_" . $object_type . "\">" . Ui::show_box_top(T_('Newest')) . "</a>";
$object_ids = Stats::get_newest($object_type, $limit, 0, 0, $user_id);
$browse     = new Browse();
$browse->set_type($object_type);
$browse->set_show_header(false);
$browse->set_grid_view(false, false);
$browse->set_mashup(true);
$browse->show_objects($object_ids);
Ui::show_box_bottom();
if ($object_type == 'podcast_episode') {
    Ui::show_box_top(T_('Popular'));
} else {
    echo "<a href=\"" . AmpConfig::get('web_path') . "/stats.php?action=popular\">" . Ui::show_box_top(T_('Popular')) . "</a>";
}
$object_ids = Stats::get_top($object_type, 100, $threshold, 0, $user_id);
shuffle($object_ids);
$object_ids = array_slice($object_ids, 0, $limit);
$browse     = new Browse();
$browse->set_type($object_type);
$browse->set_show_header(false);
$browse->set_grid_view(false, false);
$browse->set_mashup(true);
$browse->show_objects($object_ids);
Ui::show_box_bottom();
