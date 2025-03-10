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
 */

declare(strict_types=1);

namespace Ampache\Module\Catalog\GarbageCollector;

use Ampache\Module\Util\Recommendation;
use Ampache\Repository\Model\Art;
use Ampache\Repository\Model\Artist;
use Ampache\Repository\Model\Catalog;
use Ampache\Repository\Model\Label;
use Ampache\Repository\Model\Metadata\Repository\Metadata;
use Ampache\Repository\Model\Metadata\Repository\MetadataField;
use Ampache\Repository\Model\Playlist;
use Ampache\Repository\Model\Podcast_Episode;
use Ampache\Repository\Model\Rating;
use Ampache\Repository\Model\Song;
use Ampache\Repository\Model\Tag;
use Ampache\Repository\Model\Tmp_Playlist;
use Ampache\Repository\Model\Userflag;
use Ampache\Repository\Model\Video;
use Ampache\Module\Statistics\Stats;
use Ampache\Repository\AlbumRepositoryInterface;
use Ampache\Repository\Model\Wanted;
use Ampache\Repository\ShoutRepositoryInterface;
use Ampache\Repository\UserActivityRepositoryInterface;
use Ampache\Repository\UserRepositoryInterface;

/**
 * This is a wrapper for all of the different database cleaning
 * functions, it runs them in an order that resembles correctness.
 */
final class CatalogGarbageCollector implements CatalogGarbageCollectorInterface
{
    private AlbumRepositoryInterface $albumRepository;

    private ShoutRepositoryInterface $shoutRepository;

    private UserActivityRepositoryInterface $useractivityRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        AlbumRepositoryInterface $albumRepository,
        ShoutRepositoryInterface $shoutRepository,
        UserActivityRepositoryInterface $useractivityRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->albumRepository        = $albumRepository;
        $this->shoutRepository        = $shoutRepository;
        $this->useractivityRepository = $useractivityRepository;
        $this->userRepository         = $userRepository;
    }

    public function collect(): void
    {
        Song::garbage_collection();
        Artist::garbage_collection();
        $this->albumRepository->collectGarbage();
        Video::garbage_collection();
        Podcast_Episode::garbage_collection();
        Wanted::garbage_collection();
        Art::garbage_collection();
        Stats::garbage_collection();
        Rating::garbage_collection();
        Userflag::garbage_collection();
        Label::garbage_collection();
        Recommendation::garbage_collection();
        $this->useractivityRepository->collectGarbage();
        $this->userRepository->collectGarbage();
        Playlist::garbage_collection();
        Tmp_Playlist::garbage_collection();
        $this->shoutRepository->collectGarbage();
        Tag::garbage_collection();
        Catalog::clear_catalog_cache();

        // TODO: use InnoDB with foreign keys and on delete cascade to get rid of garbage collection
        Metadata::garbage_collection();
        MetadataField::garbage_collection();
    }
}
