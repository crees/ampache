<?php

/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
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

use Ampache\Config\AmpConfig;
use Ampache\Repository\Model\Share;
use Ampache\Repository\Model\User;
use Ampache\Module\Api\Api;

/**
 * Class ShareEditMethod
 * @package Lib\ApiMethods
 */
final class ShareEditMethod
{
    public const ACTION = 'share_edit';

    /**
     * share_edit
     * MINIMUM_API_VERSION=420000
     * Update the description and/or expiration date for an existing share.
     * Takes the share id to update with optional description and expires parameters.
     *
     * @param array $input
     * @param User $user
     * filter      = (string) Alpha-numeric search term
     * stream      = (boolean) 0,1 //optional
     * download    = (boolean) 0,1 //optional
     * expires     = (integer) number of whole days before expiry //optional
     * description = (string) update description //optional
     * @return boolean
     */
    public static function share_edit(array $input, User $user): bool
    {
        if (!AmpConfig::get('share')) {
            Api::error(T_('Enable: share'), '4703', self::ACTION, 'system', $input['api_format']);

            return false;
        }
        if (!Api::check_parameter($input, array('filter'), self::ACTION)) {
            return false;
        }
        $share_id = $input['filter'];
        if (in_array($share_id, Share::get_share_list($user))) {
            $share       = new Share($share_id);
            $description = (isset($input['description'])) ? filter_var($input['description'], FILTER_SANITIZE_STRING) : $share->description;
            $stream      = (isset($input['stream'])) ? filter_var($input['stream'], FILTER_SANITIZE_NUMBER_INT) : $share->allow_stream;
            $download    = (isset($input['download'])) ? filter_var($input['download'], FILTER_SANITIZE_NUMBER_INT) : $share->allow_download;
            $expires     = (isset($input['expires'])) ? filter_var($input['expires'], FILTER_SANITIZE_NUMBER_INT) : $share->expire_days;

            $data = array(
                'max_counter' => $share->max_counter,
                'expire' => $expires,
                'allow_stream' => $stream,
                'allow_download' => $download,
                'description' => $description
            );
            if ($share->update($data, $user)) {
                Api::message('share ' . $share_id . ' updated', $input['api_format']);
            } else {
                /* HINT: Requested object string/id/type ("album", "myusername", "some song title", 1298376) */
                Api::error(sprintf(T_('Bad Request: %s'), $share_id), '4710', self::ACTION, 'system', $input['api_format']);
            }
        } else {
            /* HINT: Requested object string/id/type ("album", "myusername", "some song title", 1298376) */
            Api::error(sprintf(T_('Not Found: %s'), $share_id), '4704', self::ACTION, 'filter', $input['api_format']);
        }

        return true;
    }
}
