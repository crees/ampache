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

use Ampache\Repository\Model\Preference;
use Ampache\Repository\Model\User;
use Ampache\Module\Api\Xml_Data;

/**
 * Class UserPreferencesMethod
 * @package Lib\ApiMethods
 */
final class UserPreferencesMethod
{
    public const ACTION = 'user_preferences';

    /**
     * user_preferences
     * MINIMUM_API_VERSION=5.0.0
     *
     * Get your user preferences
     *
     * @param array $input
     * @param User $user
     */
    public static function user_preferences(array $input, User $user)
    {
        // fix preferences that are missing for user
        User::fix_preferences($user->id);

        $preferences  = Preference::get_all($user->id);
        $output_array = array('preference' => $preferences);
        switch ($input['api_format']) {
            case 'json':
                echo json_encode($output_array, JSON_PRETTY_PRINT);
                break;
            default:
                echo Xml_Data::object_array($output_array['preference'], 'preference');
        }
    }
}
