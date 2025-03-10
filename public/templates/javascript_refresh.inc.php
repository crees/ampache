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

use Ampache\Module\Api\Ajax;

/** @var int $refresh_limit */
/** @var string $ajax_url */
?>
<script>
// Set refresh interval (in seconds)
var refreshInterval=<?php echo $refresh_limit ?>;

function refresh() {
    <?php echo Ajax::action($ajax_url, ''); ?>;
}
$(document).ready(function() {
    clearInterval(window.reloaditv);
    window.reloaditv = setInterval(function(){
        refresh();
    }, refreshInterval * 1000);
});

// remove the timer when navigating away
$(window).on('popstate', function() {
    clearInterval(window.reloaditv);
});
</script>
