<?php

/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
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

declare(strict_types=0);

namespace Ampache\Module\Api\Method;

use Ampache\Repository\LiveStreamRepositoryInterface;
use Ampache\Repository\Model\Live_Stream;
use Ampache\Repository\Model\User;
use Ampache\Module\Api\Api;

/**
 * Class LiveStreamDeleteMethod
 * @package Lib\ApiMethods
 */
final class LiveStreamDeleteMethod
{
    public const ACTION = 'live_stream_delete';

    /**
     * live_stream_delete
     * MINIMUM_API_VERSION=6.0.0
     *
     * Delete an existing live_stream (radio station). (if it exists)
     *
     * @param array $input
     * @param User $user
     * filter = (string) object_id to delete
     * @return boolean
     */
    public static function live_stream_delete(array $input, User $user): bool
    {
        if (!Api::check_access('interface', 50, $user->id, self::ACTION, $input['api_format'])) {
            return false;
        }
        if (!Api::check_parameter($input, array('filter'), self::ACTION)) {
            return false;
        }
        unset($user);
        $object_id = (int)$input['filter'];
        $item      = new Live_Stream($object_id);
        if (!$item->id) {
            /* HINT: Requested object string/id/type ("album", "myusername", "some song title", 1298376) */
            Api::error(sprintf(T_('Not Found: %s'), $object_id), '4704', self::ACTION, 'filter', $input['api_format']);

            return false;
        }

        $live_stream = static::getLiveStreamRepository()->delete($item->id);
        if (!$live_stream) {
            Api::error(T_('Bad Request'), '4710', self::ACTION, 'system', $input['api_format']);

            return false;
        }

        Api::message('Deleted live_stream: ' . $object_id, $input['api_format']);

        return true;
    } // live_stream_delete

    private static function getLiveStreamRepository(): LiveStreamRepositoryInterface
    {
        global $dic;

        return $dic->get(LiveStreamRepositoryInterface::class);
    }
}
