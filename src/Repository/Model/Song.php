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

declare(strict_types=0);

namespace Ampache\Repository\Model;

use Ampache\Module\Playback\Stream;
use Ampache\Module\Playback\Stream_Url;
use Ampache\Module\Song\Deletion\SongDeleterInterface;
use Ampache\Module\Song\Tag\SongTagWriterInterface;
use Ampache\Module\Statistics\Stats;
use Ampache\Module\System\Dba;
use Ampache\Module\User\Activity\UserActivityPosterInterface;
use Ampache\Module\Util\Recommendation;
use Ampache\Module\Util\Ui;
use Ampache\Repository\Model\Metadata\Metadata;
use Ampache\Module\Authorization\Access;
use Ampache\Config\AmpConfig;
use Ampache\Module\System\Core;
use PDOStatement;

class Song extends database_object implements Media, library_item, GarbageCollectibleInterface
{
    use Metadata;

    protected const DB_TABLENAME = 'song';

    /* Variables from DB */

    /**
     * @var integer $id
     */
    public $id;
    /**
     * @var string $file
     */
    public $file;
    /**
     * @var integer $album
     */
    public $album;
    /**
     * @var integer $album_disk
     */
    public $album_disk;
    /**
     * @var integer $artist
     */
    public $artist;
    /**
     * @var array $artists
     */
    public array $artists;
    /**
     * @var array $albumartists
     */
    public array $albumartists;
    /**
     * @var string $title
     */
    public $title;
    /**
     * @var integer $year
     */
    public $year;
    /**
     * @var integer $bitrate
     */
    public $bitrate;
    /**
     * @var integer $rate
     */
    public $rate;
    /**
     * @var string $mode
     */
    public $mode;
    /**
     * @var integer $size
     */
    public $size;
    /**
     * @var integer $time
     */
    public $time;
    /**
     * @var integer $track
     */
    public $track;
    /**
     * @var string $album_mbid
     */
    public $album_mbid;
    /**
     * @var string $artist_mbid
     */
    public $artist_mbid;
    /**
     * @var string $albumartist_mbid
     */
    public $albumartist_mbid;
    /**
     * @var string $type
     */
    public $type;
    /**
     * @var string $mime
     */
    public $mime;
    /**
     * @var boolean $played
     */
    public $played;
    /**
     * @var boolean $enabled
     */
    public $enabled;
    /**
     * @var integer $addition_time
     */
    public $addition_time;
    /**
     * @var integer $update_time
     */
    public $update_time;
    /**
     * MusicBrainz ID
     * @var string $mbid
     */
    public $mbid;
    /**
     * @var integer $catalog
     */
    public $catalog;
    /**
     * @var integer|null $waveform
     */
    public $waveform;
    /**
     * @var integer|null $user_upload
     */
    public $user_upload;
    /**
     * @var integer|null $license
     */
    public $license;
    /**
     * @var string $composer
     */
    public $composer;
    /**
     * @var string $catalog_number
     */
    public $catalog_number;
    /**
     * @var integer $channels
     */
    public $channels;

    /**
     * @var array $tags
     */
    public $tags;
    /**
     * @var string $label
     */
    public $label;
    /**
     * @var string $language
     */
    public $language;
    /**
     * @var string $comment
     */
    public $comment;
    /**
     * @var string $lyrics
     */
    public $lyrics;
    /**
     * @var float|null $replaygain_track_gain
     */
    public $replaygain_track_gain;
    /**
     * @var float|null $replaygain_track_peak
     */
    public $replaygain_track_peak;
    /**
     * @var float|null $replaygain_album_gain
     */
    public $replaygain_album_gain;
    /**
     * @var float|null $replaygain_album_peak
     */
    public $replaygain_album_peak;
    /**
     * @var integer|null $r128_album_gain
     */
    public $r128_album_gain;
    /**
     * @var integer|null $r128_track_gain
     */
    public $r128_track_gain;
    /**
     * @var bool $has_art
     */
    public $has_art;
    /**
     * @var string $f_name
     */
    public $f_name;
    /**
     * @var string $f_artist
     */
    public $f_artist;
    /**
     * @var string $f_album
     */
    public $f_album;
    /**
     * @var string $f_artist_full
     */
    public $f_artist_full;
    /**
     * @var integer $albumartist
     */
    public $albumartist;
    /**
     * @var string $f_albumartist_full
     */
    public $f_albumartist_full;
    /**
     * @var string $f_album_full
     */
    public $f_album_full;
    /**
     * @var string $f_time
     */
    public $f_time;
    /**
     * @var string $f_time_h
     */
    public $f_time_h;
    /**
     * @var string $f_track
     */
    public $f_track;
    /**
     * @var int $disk
     */
    public $disk;
    /**
     * @var string $f_bitrate
     */
    public $f_bitrate;
    /**
     * @var string $link
     */
    public $link;
    /**
     * @var string $f_file
     */
    public $f_file;
    /**
     * @var string $f_name_full
     */
    public $f_name_full;
    /**
     * @var string $f_link
     */
    public $f_link;
    /**
     * @var string $f_album_link
     */
    public $f_album_link;
    /**
     * @var string $f_album_disk_link
     */
    public string $f_album_disk_link;
    /**
     * @var string $f_artist_link
     */
    public $f_artist_link;
    /**
     * @var string $f_albumartist_link
     */
    public $f_albumartist_link;

    /**
     * @var string $f_year_link
     */
    public $f_year_link;

    /**
     * @var string $f_tags
     */
    public $f_tags;
    /**
     * @var string $f_size
     */
    public $f_size;
    /**
     * @var string $f_lyrics
     */
    public $f_lyrics;
    /**
     * @var string $f_pattern
     */
    public $f_pattern;
    /**
     * @var integer $count
     */
    public $count;
    /**
     * @var string $f_publisher
     */
    public $f_publisher;
    /**
     * @var string $f_composer
     */
    public $f_composer;
    /**
     * @var string $f_license
     */
    public $f_license;

    /** @var int */
    public $total_count;

    /** @var int */
    public $total_skip;

    /* Setting Variables */
    /**
     * @var boolean $_fake
     */
    public $_fake = false; // If this is a 'construct_from_array' object

    /**
     * Aliases used in insert function
     */
    public static $aliases = array(
        'mb_trackid',
        'mbid',
        'mb_albumid',
        'mb_albumid_group',
        'mb_artistid',
        'mb_albumartistid',
        'genre',
        'publisher'
    );

    /**
     * Constructor
     *
     * Song class, for modifying a song.
     * @param integer|null $songid
     * @param string $limit_threshold
     */
    public function __construct($songid = null, $limit_threshold = '')
    {
        if ($songid === null) {
            return false;
        }

        $this->id = (int)($songid);

        if (self::isCustomMetadataEnabled()) {
            $this->initializeMetadata();
        }

        $info = $this->has_info();
        if ($info !== false && is_array($info)) {
            foreach ($info as $key => $value) {
                $this->$key = $value;
            }
            $data              = pathinfo($this->file);
            $this->type        = strtolower((string)$data['extension']);
            $this->mime        = self::type_to_mime($this->type);
            $this->total_count = (int)$this->total_count;
        } else {
            $this->id = null;

            return false;
        }

        return true;
    } // constructor

    public function getId(): int
    {
        return (int)$this->id;
    }

    /**
     * insert
     *
     * This inserts the song described by the passed array
     * @param array $results
     * @return integer|boolean
     */
    public static function insert(array $results)
    {
        $check_file = Catalog::get_id_from_file($results['file'], 'song');
        if ($check_file > 0) {
            return $check_file;
        }
        $catalog          = $results['catalog'];
        $file             = $results['file'];
        $title            = Catalog::check_length(Catalog::check_title($results['title'] ?? null, $file));
        $artist           = Catalog::check_length($results['artist'] ?? null);
        $album            = Catalog::check_length($results['album'] ?? null);
        $albumartist      = Catalog::check_length($results['albumartist'] ?? null);
        $bitrate          = $results['bitrate'] ?? 0;
        $rate             = $results['rate'] ?? 0;
        $mode             = $results['mode'] ?? null;
        $size             = $results['size'] ?? 0;
        $time             = $results['time'] ?? 0;
        $track            = Catalog::check_track((string) $results['track']);
        $track_mbid       = $results['mb_trackid'] ?? $results['mbid'] ?? null;
        $album_mbid       = $results['mb_albumid'];
        $album_mbid_group = $results['mb_albumid_group'];
        $artist_mbid      = $results['mb_artistid'];
        $albumartist_mbid = $results['mb_albumartistid'];
        $disk             = (Album::sanitize_disk($results['disk']) > 0) ? Album::sanitize_disk($results['disk']) : 1;
        $year             = Catalog::normalize_year($results['year'] ?? 0);
        $comment          = $results['comment'];
        $tags             = $results['genre']; // multiple genre support makes this an array
        $lyrics           = $results['lyrics'];
        $user_upload      = $results['user_upload'] ?? null;
        $composer         = isset($results['composer']) ? Catalog::check_length($results['composer']) : null;
        $label            = isset($results['publisher']) ? Catalog::get_unique_string(Catalog::check_length($results['publisher'], 128)) : null;
        if ($label && AmpConfig::get('label')) {
            // create the label if missing
            foreach (array_map('trim', explode(';', $label)) as $label_name) {
                Label::helper($label_name);
            }
        }
        // info for the artist_map table.
        $artists_array          = $results['artists'] ?? array();
        $artist_mbid_array      = $results['mb_artistid_array'] ?? array();
        $albumartist_mbid_array = $results['mb_albumartistid_array'] ?? array();
        // if you have an artist array this will be named better than what your tags will give you
        if (!empty($artists_array)) {
            if (!empty($artist) && !empty($albumartist) && $artist == $albumartist) {
                $albumartist = $artists_array[0];
            }
            $artist = $artists_array[0];
        }
        $license_id = null;
        if (isset($results['license']) && (int)$results['license'] > 0) {
            $license_id = (int)$results['license'];
        }

        $language              = isset($results['language']) ? Catalog::check_length($results['language'], 128) : null;
        $channels              = $results['channels'] ?? null;
        $release_type          = isset($results['release_type']) ? Catalog::check_length($results['release_type'], 32) : null;
        $release_status        = $results['release_status'] ?? null;
        $replaygain_track_gain = $results['replaygain_track_gain'] ?? null;
        $replaygain_track_peak = $results['replaygain_track_peak'] ?? null;
        $replaygain_album_gain = $results['replaygain_album_gain'] ?? null;
        $replaygain_album_peak = $results['replaygain_album_peak'] ?? null;
        $r128_track_gain       = $results['r128_track_gain'] ?? null;
        $r128_album_gain       = $results['r128_album_gain'] ?? null;
        $original_year         = Catalog::normalize_year($results['original_year'] ?? 0);
        $barcode               = Catalog::check_length($results['barcode'], 64);
        $catalog_number        = isset($results['catalog_number']) ? Catalog::check_length($results['catalog_number'], 64) : null;
        $version               = Catalog::check_length($results['version'], 64);

        if (!in_array($mode, ['vbr', 'cbr', 'abr'])) {
            debug_event(self::class, 'Error analyzing: ' . $file . ' unknown file bitrate mode: ' . $mode, 2);
            $mode = null;
        }
        if (!isset($results['albumartist_id'])) {
            $albumartist_id = null;
            if ($albumartist) {
                $albumartist_mbid = Catalog::trim_slashed_list($albumartist_mbid);
                $albumartist_id   = Artist::check($albumartist, $albumartist_mbid);
            }
        } else {
            $albumartist_id = (int)($results['albumartist_id']);
        }
        if (!isset($results['artist_id'])) {
            $artist_mbid = Catalog::trim_slashed_list($artist_mbid);
            $artist_id   = Artist::check($artist, $artist_mbid);
        } else {
            $artist_id = (int)($results['artist_id']);
        }
        if (!isset($results['album_id'])) {
            $album_id = Album::check($catalog, $album, $year, $album_mbid, $album_mbid_group, $albumartist_id, $release_type, $release_status, $original_year, $barcode, $catalog_number, $version);
        } else {
            $album_id = (int)($results['album_id']);
        }
        $insert_time = time();

        $sql = "INSERT INTO `song` (`catalog`, `file`, `album`, `disk`, `artist`, `title`, `bitrate`, `rate`, `mode`, `size`, `time`, `track`, `addition_time`, `update_time`, `year`, `mbid`, `user_upload`, `license`, `composer`, `channels`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $db_results = Dba::write($sql, array(
            $catalog,
            $file,
            $album_id,
            $disk,
            $artist_id,
            $title,
            $bitrate,
            $rate,
            $mode,
            $size,
            $time,
            $track,
            $insert_time,
            $insert_time,
            $year,
            $track_mbid,
            $user_upload,
            $license_id,
            $composer,
            $channels
        ));

        if (!$db_results) {
            debug_event(self::class, 'Unable to insert ' . $file, 2);

            return false;
        }

        $song_id = (int)Dba::insert_id();
        $artists = array((int)$artist_id, (int)$albumartist_id);

        // create the album_disk
        $sql = "INSERT IGNORE INTO `album_disk` (`album_id`, `disk`, `catalog`) VALUES(?, ?, ?)";
        Dba::write($sql, array($song_id, $disk, $catalog));
        // map the song to catalog album and artist maps
        Catalog::update_map((int)$catalog, 'song', $song_id);
        if ((int)$artist_id > 0) {
            Artist::add_artist_map($artist_id, 'song', $song_id);
            Album::add_album_map($album_id, 'song', $artist_id);
        }
        if ((int)$albumartist_id > 0) {
            Artist::add_artist_map($albumartist_id, 'album', $album_id);
            Album::add_album_map($album_id, 'album', $albumartist_id);
        }
        foreach ($artist_mbid_array as $songArtist_mbid) {
            $song_artist_id = Artist::check_mbid($songArtist_mbid);
            if ($song_artist_id > 0) {
                $artists[] = $song_artist_id;
                Artist::add_artist_map($song_artist_id, 'song', $song_id);
                Album::add_album_map($album_id, 'song', $song_artist_id);
            }
        }
        // add song artists found by name to the list (Ignore artist names when we have the same amount of MBID's)
        if (!empty($artists_array) && !count($artists_array) == count($artist_mbid_array)) {
            foreach ($artists_array as $artist_name) {
                $song_artist_id = Artist::check($artist_name);
                if ($song_artist_id > 0) {
                    $artists[] = $song_artist_id;
                    Artist::add_artist_map($song_artist_id, 'song', $song_id);
                    Album::add_album_map($album_id, 'song', $song_artist_id);
                }
            }
        }
        foreach ($albumartist_mbid_array as $albumArtist_mbid) {
            $album_artist_id = Artist::check_mbid($albumArtist_mbid);
            if ($album_artist_id > 0) {
                $artists[] = $album_artist_id;
                Artist::add_artist_map($album_artist_id, 'album', $album_id);
                Album::add_album_map($album_id, 'album', $album_artist_id);
            }
        }
        // update the all the counts for the album right away
        Album::update_album_count($album_id);

        if ($user_upload) {
            static::getUserActivityPoster()->post((int) $user_upload, 'upload', 'song', (int) $song_id, time());
        }

        // Allow scripts to populate new tags when injecting user uploads
        if (!defined('NO_SESSION')) {
            if ($user_upload && !Access::check('interface', 50, $user_upload)) {
                $tags = Tag::clean_to_existing($tags);
            }
        }
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $tag = trim((string)$tag);
                if (!empty($tag)) {
                    Tag::add('song', $song_id, $tag, false);
                    Tag::add('album', $album_id, $tag, false);
                    foreach (array_unique($artists) as $found_artist_id) {
                        if ($found_artist_id > 0) {
                            Tag::add('artist', $found_artist_id, $tag, false);
                        }
                    }
                }
            }
        }

        $sql = "INSERT INTO `song_data` (`song_id`, `comment`, `lyrics`, `label`, `language`, `replaygain_track_gain`, `replaygain_track_peak`, `replaygain_album_gain`, `replaygain_album_peak`, `r128_track_gain`, `r128_album_gain`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        Dba::write($sql, array($song_id, $comment, $lyrics, $label, $language, $replaygain_track_gain, $replaygain_track_peak, $replaygain_album_gain, $replaygain_album_peak, $r128_track_gain, $r128_album_gain));

        return $song_id;
    }

    /**
     * garbage_collection
     *
     * Cleans up the song_data table
     */
    public static function garbage_collection()
    {
        // delete files matching catalog_ignore_pattern
        $ignore_pattern = AmpConfig::get('catalog_ignore_pattern');
        if ($ignore_pattern) {
            Dba::write("DELETE FROM `song` WHERE `file` REGEXP ?;", array($ignore_pattern));
        }
        // delete duplicates
        Dba::write("DELETE `dupe` FROM `song` AS `dupe`, `song` AS `orig` WHERE `dupe`.`id` > `orig`.`id` AND `dupe`.`file` <=> `orig`.`file`;");
        // clean up missing catalogs
        Dba::write("DELETE FROM `song` WHERE `song`.`catalog` NOT IN (SELECT `id` FROM `catalog`);");
        // delete the rest
        Dba::write("DELETE FROM `song_data` WHERE `song_data`.`song_id` NOT IN (SELECT `song`.`id` FROM `song`);");
        // also clean up some bad data that might creep in
        Dba::write("UPDATE `song` SET `composer` = NULL WHERE `composer` = '';");
        Dba::write("UPDATE `song` SET `mbid` = NULL WHERE `mbid` = '';");
        Dba::write("UPDATE `song_data` SET `comment` = NULL WHERE `comment` = '';");
        Dba::write("UPDATE `song_data` SET `lyrics` = NULL WHERE `lyrics` = '';");
        Dba::write("UPDATE `song_data` SET `label` = NULL WHERE `label` = '';");
        Dba::write("UPDATE `song_data` SET `language` = NULL WHERE `language` = '';");
        Dba::write("UPDATE `song_data` SET `waveform` = NULL WHERE `waveform` = '';");
    }

    /**
     * build_cache
     *
     * This attempts to reduce queries by asking for everything in the
     * browse all at once and storing it in the cache, this can help if the
     * db connection is the slow point.
     * @param integer[] $song_ids
     * @param string $limit_threshold
     * @return boolean
     */
    public static function build_cache($song_ids, $limit_threshold = '')
    {
        if (empty($song_ids)) {
            return false;
        }

        $idlist = '(' . implode(',', $song_ids) . ')';
        if ($idlist == '()') {
            return false;
        }
        $artists = array();
        $albums  = array();
        $tags    = array();

        // Song data cache
        $sql   = (AmpConfig::get('catalog_disable'))
            ? "SELECT `song`.`id`, `file`, `catalog`, `album`, `year`, `artist`, `title`, `bitrate`, `rate`, `mode`, `size`, `time`, `track`, `played`, `song`.`enabled`, `update_time`, `tag_map`.`tag_id`, `mbid`, `addition_time`, `license`, `composer`, `user_upload`, `song`.`total_count`, `song`.`total_skip` FROM `song` LEFT JOIN `tag_map` ON `tag_map`.`object_id`=`song`.`id` AND `tag_map`.`object_type`='song' LEFT JOIN `catalog` ON `catalog`.`id` = `song`.`catalog` WHERE `song`.`id` IN $idlist AND `catalog`.`enabled` = '1' "
            : "SELECT `song`.`id`, `file`, `catalog`, `album`, `year`, `artist`, `title`, `bitrate`, `rate`, `mode`, `size`, `time`, `track`, `played`, `song`.`enabled`, `update_time`, `tag_map`.`tag_id`, `mbid`, `addition_time`, `license`, `composer`, `user_upload`, `song`.`total_count`, `song`.`total_skip` FROM `song` LEFT JOIN `tag_map` ON `tag_map`.`object_id`=`song`.`id` AND `tag_map`.`object_type`='song' WHERE `song`.`id` IN $idlist";

        $db_results = Dba::read($sql);
        while ($row = Dba::fetch_assoc($db_results)) {
            if (AmpConfig::get('show_played_times')) {
                $row['total_count'] = (!empty($limit_threshold))
                    ? Stats::get_object_count('song', $row['id'], $limit_threshold)
                    : $row['total_count'];
            }
            if (AmpConfig::get('show_skipped_times')) {
                $row['total_skip'] = (!empty($limit_threshold))
                    ? Stats::get_object_count('song', $row['id'], $limit_threshold, 'skip')
                    : $row['total_skip'];
            }
            parent::add_to_cache('song', $row['id'], $row);
            $artists[$row['artist']] = $row['artist'];
            $albums[$row['album']]   = $row['album'];
            if ($row['tag_id']) {
                $tags[$row['tag_id']] = $row['tag_id'];
            }
        }

        Artist::build_cache($artists);
        Album::build_cache($albums);
        Tag::build_cache($tags);
        Tag::build_map_cache('song', $song_ids);
        Art::build_cache($albums);

        // If we're rating this then cache them as well
        if (AmpConfig::get('ratings')) {
            Rating::build_cache('song', $song_ids);
            Userflag::build_cache('song', $song_ids);
        }

        // Build a cache for the song's extended table
        $sql        = "SELECT * FROM `song_data` WHERE `song_id` IN $idlist";
        $db_results = Dba::read($sql);

        while ($row = Dba::fetch_assoc($db_results)) {
            parent::add_to_cache('song_data', $row['song_id'], $row);
        }

        return true;
    } // build_cache

    /**
     * has_info
     * @return array|boolean
     */
    private function has_info()
    {
        $song_id = $this->id;

        if (parent::is_cached('song', $song_id) && !empty(parent::get_from_cache('song', $song_id)['disk'])) {
            return parent::get_from_cache('song', $song_id);
        }

        $sql        = "SELECT `song`.`id`, `song`.`file`, `song`.`catalog`, `song`.`album`, `song`.`disk`, `song`.`year`, `song`.`artist`, `song`.`title`, `song`.`bitrate`, `song`.`rate`, `song`.`mode`, `song`.`size`, `song`.`time`, `song`.`track`, `song`.`mbid`, `song`.`played`, `song`.`enabled`, `song`.`update_time`, `song`.`addition_time`, `song`.`license`, `song`.`composer`, `song`.`channels`, `song`.`total_count`, `song`.`total_skip`, `album`.`album_artist` AS `albumartist`, `song`.`user_upload`, `album`.`mbid` AS `album_mbid`, `artist`.`mbid` AS `artist_mbid`, `album_artist`.`mbid` AS `albumartist_mbid` FROM `song` LEFT JOIN `album` ON `album`.`id` = `song`.`album` LEFT JOIN `artist` ON `artist`.`id` = `song`.`artist` LEFT JOIN `artist` AS `album_artist` ON `album_artist`.`id` = `album`.`album_artist` WHERE `song`.`id` = ?";
        $db_results = Dba::read($sql, array($song_id));
        $results    = Dba::fetch_assoc($db_results);
        if (isset($results['id'])) {
            parent::add_to_cache('song', $song_id, $results);

            return $results;
        }

        return false;
    }

    /**
     * has_id
     * @param int|string $song_id
     * @return boolean
     */
    public static function has_id($song_id)
    {
        $sql        = "SELECT `song`.`id` FROM `song` WHERE `song`.`id` = ?";
        $db_results = Dba::read($sql, array($song_id));
        $results    = Dba::fetch_assoc($db_results);
        if (isset($results['id'])) {
            return true;
        }

        return false;
    }

    /**
     * can_scrobble
     *
     * return a song id based on a last.fm-style search in the database
     * @param string $song_name
     * @param string $artist_name
     * @param string $album_name
     * @param string $song_mbid
     * @param string $artist_mbid
     * @param string $album_mbid
     * @return string
     */
    public static function can_scrobble(
        $song_name,
        $artist_name,
        $album_name,
        $song_mbid = '',
        $artist_mbid = '',
        $album_mbid = ''
    ) {
        // by default require song, album, artist for any searches
        $sql    = "SELECT `song`.`id` FROM `song` LEFT JOIN `album` ON `album`.`id` = `song`.`album` LEFT JOIN `artist` ON `artist`.`id` = `song`.`artist` WHERE `song`.`title` = ? AND (`artist`.`name` = ? OR LTRIM(CONCAT(COALESCE(`artist`.`prefix`, ''), ' ', `artist`.`name`)) = ?) AND (`album`.`name` = ? OR LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`)) = ?)";
        $params = array($song_name, $artist_name, $artist_name, $album_name, $album_name);
        if (!empty($song_mbid)) {
            $sql .= " AND `song`.`mbid` = ?";
            $params[] = $song_mbid;
        }
        if (!empty($artist_mbid)) {
            $sql .= " AND `artist`.`mbid` = ?";
            $params[] = $artist_mbid;
        }
        if (!empty($album_mbid)) {
            $sql .= " AND `album`.`mbid` = ?";
            $params[] = $album_mbid;
        }
        $sql .= " LIMIT 1;";
        $db_results = Dba::read($sql, $params);
        $row        = Dba::fetch_assoc($db_results);
        if (empty($row)) {
            debug_event(self::class, 'can_scrobble failed to find: ' . $song_name, 5);

            return '';
        }

        return $row['id'];
    }

    /**
     * _get_ext_info
     * This function gathers information from the song_ext_info table and adds it to the
     * current object
     * @param string $select
     * @return array
     */
    public function _get_ext_info($select = '')
    {
        $song_id = (int) ($this->id);
        $columns = (!empty($select)) ? Dba::escape($select) : '*';

        if (parent::is_cached('song_data', $song_id)) {
            return parent::get_from_cache('song_data', $song_id);
        }

        $sql        = "SELECT $columns FROM `song_data` WHERE `song_id` = ?";
        $db_results = Dba::read($sql, array($song_id));

        $results = Dba::fetch_assoc($db_results);

        parent::add_to_cache('song_data', $song_id, $results);

        return $results;
    } // _get_ext_info

    /**
     * fill_ext_info
     * This calls the _get_ext_info and then sets the correct vars
     * @param string $data_filter
     */
    public function fill_ext_info($data_filter = '')
    {
        $info = $this->_get_ext_info($data_filter);
        if (!empty($info)) {
            foreach ($info as $key => $value) {
                if ($key != 'song_id') {
                    $this->$key = $value;
                }
            } // end foreach
        }
    } // fill_ext_info

    /**
     * type_to_mime
     *
     * Returns the mime type for the specified file extension/type
     * @param string $type
     * @return string
     */
    public static function type_to_mime($type)
    {
        // FIXME: This should really be done the other way around.
        // Store the mime type in the database, and provide a function
        // to make it a human-friendly type.
        switch ($type) {
            case 'spx':
            case 'ogg':
                return 'application/ogg';
            case 'opus':
                return 'audio/ogg; codecs=opus';
            case 'wma':
            case 'asf':
                return 'audio/x-ms-wma';
            case 'rm':
            case 'ra':
                return 'audio/x-realaudio';
            case 'flac':
                return 'audio/x-flac';
            case 'wv':
                return 'audio/x-wavpack';
            case 'aac':
            case 'mp4':
            case 'm4a':
            case 'm4b':
                return 'audio/mp4';
            case 'aacp':
                return 'audio/aacp';
            case 'mpc':
                return 'audio/x-musepack';
            case 'mkv':
                return 'audio/x-matroska';
            case 'mpeg3':
            case 'mp3':
            default:
                return 'audio/mpeg';
        }
    }

    /**
     * get_disabled
     *
     * Gets a list of the disabled songs for and returns an array of Songs
     * @param integer $count
     * @return Song[]
     */
    public static function get_disabled($count = 0)
    {
        $results = array();

        $sql = "SELECT `id` FROM `song` WHERE `enabled`='0'";
        if ($count) {
            $sql .= " LIMIT $count";
        }
        $db_results = Dba::read($sql);

        while ($row = Dba::fetch_assoc($db_results)) {
            $results[] = new Song($row['id']);
        }

        return $results;
    }

    /**
     * find
     * @param array $data
     * @return boolean
     */
    public static function find($data)
    {
        $sql_base = "SELECT `song`.`id` FROM `song`";
        if ($data['mb_trackid']) {
            $sql        = $sql_base . " WHERE `song`.`mbid` = ? LIMIT 1";
            $db_results = Dba::read($sql, array($data['mb_trackid']));
            if ($results = Dba::fetch_assoc($db_results)) {
                return $results['id'];
            }
        }
        if ($data['file']) {
            $sql        = $sql_base . " WHERE `song`.`file` = ? LIMIT 1";
            $db_results = Dba::read($sql, array($data['file']));
            if ($results = Dba::fetch_assoc($db_results)) {
                return $results['id'];
            }
        }

        $where  = "WHERE `song`.`title` = ?";
        $sql    = $sql_base;
        $params = array($data['title']);
        if ($data['track']) {
            $where .= " AND `song`.`track` = ?";
            $params[] = $data['track'];
        }
        $sql .= " INNER JOIN `artist` ON `artist`.`id` = `song`.`artist`";
        $sql .= " INNER JOIN `album` ON `album`.`id` = `song`.`album`";

        if ($data['mb_artistid']) {
            $where .= " AND `artist`.`mbid` = ?";
            $params[] = $data['mb_artistid'];
        } else {
            $where .= " AND `artist`.`name` = ?";
            $params[] = $data['artist'];
        }
        if ($data['mb_albumid']) {
            $where .= " AND `album`.`mbid` = ?";
            $params[] = $data['mb_albumid'];
        } else {
            $where .= " AND `album`.`name` = ?";
            $params[] = $data['album'];
        }

        $sql .= $where . " LIMIT 1";
        $db_results = Dba::read($sql, $params);
        if ($results = Dba::fetch_assoc($db_results)) {
            return $results['id'];
        }

        return false;
    }

    /**
     * Get duplicate information.
     * @param array $dupe
     * @param string $search_type
     * @return integer[]
     */
    public static function get_duplicate_info($dupe, $search_type)
    {
        $results = array();
        if (isset($dupe['id'])) {
            $results[] = $dupe['id'];
        } else {
            $sql = "SELECT `id` FROM `song` WHERE `title`='" . Dba::escape($dupe['title']) . "' ";

            if ($search_type == 'artist_title' || $search_type == 'artist_album_title') {
                $sql .= "AND `artist`='" . Dba::escape($dupe['artist']) . "' ";
            }
            if ($search_type == 'artist_album_title') {
                $sql .= "AND `album` = '" . Dba::escape($dupe['album']) . "' ";
            }
            $sql .= 'ORDER BY `time`, `bitrate`, `size`';

            if ($search_type == 'album') {
                $sql = "SELECT `id` FROM `song` LEFT JOIN (SELECT MIN(`id`) AS `dupe_id1`, LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`)) AS `fullname`, COUNT(LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`))) AS `Counting` FROM `album` GROUP BY `album_artist`, LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`)) HAVING `Counting` > 1) AS `dupe_search` ON `song`.`album` = `dupe_search`.`dupe_id1` LEFT JOIN (SELECT MAX(`id`) AS `dupe_id2`, LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`)) AS `fullname`, COUNT(LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`))) AS `Counting` FROM `album` GROUP BY `album_artist`, LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`)) HAVING `Counting` > 1) AS `dupe_search2` ON `song`.`album` = `dupe_search2`.`dupe_id2` WHERE `dupe_search`.`dupe_id1` IS NOT NULL OR `dupe_search2`.`dupe_id2` IS NOT NULL ORDER BY `album`, `track`";
            }

            $db_results = Dba::read($sql);

            while ($item = Dba::fetch_assoc($db_results)) {
                $results[] = (int)$item['id'];
            } // end while
        }

        return $results;
    }

    /**
     * get_album_fullname
     * gets the name of $this->album, allows passing of id
     * @param integer $album_id
     * @param bool $simple
     * @return string
     */
    public function get_album_fullname($album_id = 0, $simple = false)
    {
        if (isset($this->f_album_full) && $album_id == 0) {
            return $this->f_album_full;
        }
        $album = (!$album_id)
            ? new Album($this->album)
            : new Album($album_id);
        $this->f_album_full = $album->get_fullname($simple);

        return $this->f_album_full;
    } // get_album_fullname

    /**
     * get_album_disk_fullname
     * gets the name of $this->album, allows passing of id
     * @return string
     */
    public function get_album_disk_fullname()
    {
        $albumDisk = new AlbumDisk($this->get_album_disk());

        return $albumDisk->get_fullname();
    } // get_album_disk_fullname

    /**
     * get_album_catalog_number
     * gets the catalog_number of $this->album, allows passing of id
     * @param integer $album_id
     * @return string
     */
    public function get_album_catalog_number($album_id = null)
    {
        if ($album_id === null) {
            $album_id = $this->album;
        }
        $album = new Album($album_id);

        return $album->catalog_number;
    } // get_album_catalog_number

    /**
     * get_album_original_year
     * gets the original_year of $this->album, allows passing of id
     * @param integer $album_id
     * @return integer
     */
    public function get_album_original_year($album_id = null)
    {
        if ($album_id === null) {
            $album_id = $this->album;
        }
        $album = new Album($album_id);

        return $album->original_year;
    } // get_album_original_year

    /**
     * get_album_barcode
     * gets the barcode of $this->album, allows passing of id
     * @param integer $album_id
     * @return string
     */
    public function get_album_barcode($album_id = null)
    {
        if (!$album_id) {
            $album_id = $this->album;
        }
        $album = new Album($album_id);

        return $album->barcode;
    } // get_album_barcode

    /**
     * get_artist_fullname
     * gets the name of $this->artist, allows passing of id
     * @param int $artist_id
     * @return string
     */
    public function get_artist_fullname($artist_id = 0)
    {
        if ($artist_id > 0) {
            return Artist::get_fullname_by_id($artist_id);
        }
        if (!isset($this->f_artist_full)) {
            $this->f_artist_full = Artist::get_fullname_by_id($this->artist);
        }

        return $this->f_artist_full;
    } // get_artist_fullname

    /**
     * get_album_artist_fullname
     * gets the name of $this->albumartist, allows passing of id
     * @param integer $album_artist_id
     * @return string
     */
    public function get_album_artist_fullname($album_artist_id = 0)
    {
        if ($album_artist_id) {
            return self::get_artist_fullname($album_artist_id);
        }
        if ($this->albumartist == null) {
            return '';
        }
        if (!isset($this->albumartist)) {
            $this->albumartist = Album::get_album_artist($this->album);
        }

        return self::get_artist_fullname($this->albumartist);
    } // get_album_artist_fullname

    /**
     * get_album_disk
     * gets album_disk of the object
     * @return int
     */
    public function get_album_disk()
    {
        if (isset($this->album_disk)) {
            return $this->album_disk;
        }
        $sql        = "SELECT DISTINCT `id` FROM `album_disk` WHERE `album_id` = ? AND `disk` = ?;";
        $db_results = Dba::read($sql, array($this->album, $this->disk));
        $results    = Dba::fetch_assoc($db_results);
        if (empty($results)) {
            return null;
        }
        $this->album_disk = (int)$results['id'];

        return $this->album_disk;
    } // get_album_artist_fullname

    /**
     * set_played
     * this checks to see if the current object has been played
     * if not then it sets it to played. In any case it updates stats.
     * @param integer $user_id
     * @param string $agent
     * @param array $location
     * @param integer $date
     * @return boolean
     */
    public function set_played($user_id, $agent, $location, $date = null)
    {
        // ignore duplicates or skip the last track
        if (!$this->check_play_history($user_id, $agent, $date)) {
            return false;
        }
        // insert stats for each object type
        if (Stats::insert('song', $this->id, $user_id, $agent, $location, 'stream', $date)) {
            // followup on some stats too
            Stats::insert('album', $this->album, $user_id, $agent, $location, 'stream', $date);
            // insert plays for song and album artists
            $artists = array_unique(array_merge(self::get_parent_array($this->id), self::get_parent_array($this->album, 'album')));
            foreach ($artists as $artist_id) {
                Stats::insert('artist', $artist_id, $user_id, $agent, $location, 'stream', $date);
            }
            // running total of the user stream data
            $play_size = (int)(User::get_user_data($user_id, 'play_size')['play_size'] ?? 0);
            User::set_user_data($user_id, 'play_size', ($play_size + $this->size));
        }
        // If it hasn't been played, set it
        if (!$this->played) {
            self::update_played(true, $this->id);
        }

        return true;
    } // set_played

    /**
     * check_play_history
     * this checks to see if the current object has been played
     * if not then it sets it to played. In any case it updates stats.
     * @param integer $user
     * @param string $agent
     * @param integer $date
     * @return boolean
     */
    public function check_play_history($user, $agent, $date)
    {
        return Stats::has_played_history('song', $this, $user, $agent, $date);
    }

    /**
     * compare_song_information
     * this compares the new ID3 tags of a file against
     * the ones in the database to see if they have changed
     * it returns false if nothing has changes, or the true
     * if they have. Static because it doesn't need this
     * @param Song $song
     * @param Song $new_song
     * @return array
     */
    public static function compare_song_information(Song $song, Song $new_song)
    {
        // Remove some stuff we don't care about as this function only needs to check song information.
        unset($song->catalog, $song->played, $song->enabled, $song->addition_time, $song->update_time, $song->type);
        $string_array = array('title', 'comment', 'lyrics', 'composer', 'tags', 'artist', 'album', 'album_disk', 'time');
        $skip_array   = array(
            'id',
            'tag_id',
            'mime',
            'mbid',
            'waveform',
            'total_count',
            'total_skip',
            'albumartist',
            'artist_mbid',
            'album_mbid',
            'albumartist_mbid',
            'mb_albumid_group',
            'disabledMetadataFields'
        );

        return self::compare_media_information($song, $new_song, $string_array, $skip_array);
    } // compare_song_information

    /**
     * compare_media_information
     * @param $media
     * @param $new_media
     * @param string[] $string_array
     * @param string[] $skip_array
     * @return array
     */
    public static function compare_media_information($media, $new_media, $string_array, $skip_array)
    {
        $array            = array();
        $array['change']  = false;
        $array['element'] = array();

        // Pull out all the currently set vars
        $fields = get_object_vars($media);

        // Foreach them
        foreach ($fields as $key => $value) {
            $key = trim((string)$key);
            if (empty($key) || in_array($key, $skip_array)) {
                continue;
            }

            // Represent the value as a string for simpler comparison. For array, ensure to sort similarly old/new values
            if (is_array($media->$key)) {
                $arr = $media->$key;
                sort($arr);
                $mediaData = implode(" ", $arr);
            } else {
                $mediaData = $media->$key;
            }

            // Skip the item if it is no string nor something we can turn into a string
            if (!is_string($mediaData) && !is_numeric($mediaData) && !is_bool($mediaData)) {
                if (is_object($mediaData) && !method_exists($mediaData, '__toString')) {
                    continue;
                }
            }

            if (is_array($new_media->$key)) {
                $arr = $new_media->$key;
                sort($arr);
                $newMediaData = implode(" ", $arr);
            } else {
                $newMediaData = $new_media->$key;
            }

            // If it's a string thing
            if (in_array($key, $string_array)) {
                $mediaData    = self::clean_string_field_value($mediaData);
                $newMediaData = self::clean_string_field_value($newMediaData);
                if ($mediaData != $newMediaData) {
                    $array['change']        = true;
                    $array['element'][$key] = 'OLD: ' . $mediaData . ' --> ' . $newMediaData;
                }
            } // in array of strings
            elseif ($newMediaData !== null) {
                if ($media->$key != $new_media->$key) {
                    $array['change']        = true;
                    $array['element'][$key] = 'OLD:' . $mediaData . ' --> ' . $newMediaData;
                }
            } // end else
        } // end foreach

        if ($array['change']) {
            debug_event(self::class, 'media-diff ' . json_encode($array['element']), 5);
        }

        return $array;
    }

    /**
     * clean_string_field_value
     * @param string $value
     * @return string
     */
    private static function clean_string_field_value($value)
    {
        if (!$value) {
            return '';
        }
        $value = trim(stripslashes(preg_replace('/\s+/', ' ', $value)));

        // Strings containing  only UTF-8 BOM = empty string
        if (strlen((string)$value) == 2 && (ord($value[0]) == 0xFF || ord($value[0]) == 0xFE)) {
            $value = "";
        }

        return $value;
    }

    /**
     * update
     * This takes a key'd array of data does any cleaning it needs to
     * do and then calls the helper functions as needed.
     * @param array $data
     * @return integer
     */
    public function update(array $data)
    {
        foreach ($data as $key => $value) {
            debug_event(self::class, $key . '=' . $value, 5);

            switch ($key) {
                case 'artist_name':
                    // Create new artist name and id
                    $old_artist_id = $this->artist;
                    $new_artist_id = Artist::check($value);
                    $this->artist  = $new_artist_id;
                    self::update_artist($new_artist_id, $this->id, $old_artist_id);
                    break;
                case 'album_name':
                    // Create new album name and id
                    $old_album_id = $this->album;
                    $new_album_id = Album::check($this->catalog, $value);
                    $this->album  = $new_album_id;
                    self::update_album($new_album_id, $this->id, $old_album_id);
                    break;
                case 'artist':
                    // Change artist the song is assigned to
                    if ($value != $this->$key) {
                        $old_artist_id = $this->artist;
                        $new_artist_id = $value;
                        self::update_artist($new_artist_id, $this->id, $old_artist_id);
                    }
                    break;
                case 'album':
                    // Change album the song is assigned to
                    if ($value != $this->$key) {
                        $old_album_id = $this->$key;
                        $new_album_id = $value;
                        self::update_album($new_album_id, $this->id, $old_album_id);
                    }
                    break;
                case 'year':
                case 'title':
                case 'track':
                case 'mbid':
                case 'license':
                case 'composer':
                case 'label':
                case 'language':
                case 'comment':
                    // Check to see if it needs to be updated
                    if ($value != $this->$key) {
                        $function = 'update_' . $key;
                        self::$function($value, $this->id);
                        $this->$key = $value;
                    }
                    break;
                case 'edit_tags':
                    Tag::update_tag_list($value, 'song', $this->id, true);
                    $this->tags = Tag::get_top_tags('song', $this->id);
                    break;
                case 'metadata':
                    if (self::isCustomMetadataEnabled()) {
                        $this->updateMetadata($value);
                    }
                    break;
                default:
                    break;
            } // end whitelist
        } // end foreach

        $this->getSongTagWriter()->write(
            $this
        );

        return $this->id;
    } // update

    /**
     * update_song
     * this is the main updater for a song and updates
     * the "update_time" of the song
     * @param integer $song_id
     * @param Song $new_song
     */
    public static function update_song($song_id, Song $new_song)
    {
        $update_time = time();

        $sql = "UPDATE `song` SET `album` = ?, `disk` = ?, `year` = ?, `artist` = ?, `title` = ?, `composer` = ?, `bitrate` = ?, `rate` = ?, `mode` = ?, `size` = ?, `time` = ?, `track` = ?, `mbid` = ?, `update_time` = ? WHERE `id` = ?";
        Dba::write($sql, array($new_song->album, $new_song->disk, $new_song->year, $new_song->artist,
                                $new_song->title, $new_song->composer, (int) $new_song->bitrate, (int) $new_song->rate, $new_song->mode,
                                (int) $new_song->size, (int) $new_song->time, $new_song->track, $new_song->mbid,
                                $update_time, $song_id));

        $sql = "UPDATE `song_data` SET `label` = ?, `lyrics` = ?, `language` = ?, `comment` = ?, `replaygain_track_gain` = ?, `replaygain_track_peak` = ?, `replaygain_album_gain` = ?, `replaygain_album_peak` = ?, `r128_track_gain` = ?, `r128_album_gain` = ? WHERE `song_id` = ?";
        Dba::write($sql, array($new_song->label, $new_song->lyrics, $new_song->language, $new_song->comment, $new_song->replaygain_track_gain,
            $new_song->replaygain_track_peak, $new_song->replaygain_album_gain, $new_song->replaygain_album_peak, $new_song->r128_track_gain, $new_song->r128_album_gain, $song_id));
    } // update_song

    /**
     * update_year
     * update the year tag
     * @param integer $new_year
     * @param integer $song_id
     */
    public static function update_year($new_year, $song_id)
    {
        self::_update_item('year', $new_year, $song_id, 50, true);
    } // update_year

    /**
     * update_label
     * This updates the label tag of the song
     * @param string $new_value
     * @param integer $song_id
     */
    public static function update_label($new_value, $song_id)
    {
        self::_update_ext_item('label', $new_value, $song_id, 50, true);
    } // update_label

    /**
     * update_language
     * This updates the language tag of the song
     * @param string $new_lang
     * @param integer $song_id
     */
    public static function update_language($new_lang, $song_id)
    {
        self::_update_ext_item('language', $new_lang, $song_id, 50, true);
    } // update_language

    /**
     * update_comment
     * updates the comment field
     * @param string $new_comment
     * @param integer $song_id
     */
    public static function update_comment($new_comment, $song_id)
    {
        self::_update_ext_item('comment', $new_comment, $song_id, 50, true);
    } // update_comment

    /**
     * update_lyrics
     * updates the lyrics field
     * @param string $new_lyrics
     * @param integer $song_id
     */
    public static function update_lyrics($new_lyrics, $song_id)
    {
        self::_update_ext_item('lyrics', $new_lyrics, $song_id, 50, true);
    } // update_lyrics

    /**
     * update_title
     * updates the title field
     * @param string $new_title
     * @param integer $song_id
     */
    public static function update_title($new_title, $song_id)
    {
        self::_update_item('title', $new_title, $song_id, 50, true);
    } // update_title

    /**
     * update_composer
     * updates the composer field
     * @param string $new_composer
     * @param integer $song_id
     */
    public static function update_composer($new_composer, $song_id)
    {
        self::_update_item('composer', $new_composer, $song_id, 50, true);
    } // update_composer

    /**
     * update_publisher
     * updates the publisher field
     * @param string $new_publisher
     * @param integer $song_id
     */
    public static function update_publisher($new_publisher, $song_id)
    {
        self::_update_item('publisher', $new_publisher, $song_id, 50, true);
    } // update_publisher

    /**
     * update_bitrate
     * updates the bitrate field
     * @param integer $new_bitrate
     * @param integer $song_id
     */
    public static function update_bitrate($new_bitrate, $song_id)
    {
        self::_update_item('bitrate', $new_bitrate, $song_id, 50, true);
    } // update_bitrate

    /**
     * update_rate
     * updates the rate field
     * @param integer $new_rate
     * @param integer $song_id
     */
    public static function update_rate($new_rate, $song_id)
    {
        self::_update_item('rate', $new_rate, $song_id, 50, true);
    } // update_rate

    /**
     * update_mode
     * updates the mode field
     * @param string $new_mode
     * @param integer $song_id
     */
    public static function update_mode($new_mode, $song_id)
    {
        self::_update_item('mode', $new_mode, $song_id, 50, true);
    } // update_mode

    /**
     * update_size
     * updates the size field
     * @param integer $new_size
     * @param integer $song_id
     */
    public static function update_size($new_size, $song_id)
    {
        self::_update_item('size', $new_size, $song_id, 50);
    } // update_size

    /**
     * update_time
     * updates the time field
     * @param integer $new_time
     * @param integer $song_id
     */
    public static function update_time($new_time, $song_id)
    {
        self::_update_item('time', $new_time, $song_id, 50, true);
    } // update_time

    /**
     * update_track
     * this updates the track field
     * @param integer $new_track
     * @param integer $song_id
     */
    public static function update_track($new_track, $song_id)
    {
        self::_update_item('track', $new_track, $song_id, 50, true);
    } // update_track

    /**
     * update_mbid
     * updates mbid field
     * @param string $new_mbid
     * @param integer $song_id
     */
    public static function update_mbid($new_mbid, $song_id)
    {
        self::_update_item('mbid', $new_mbid, $song_id, 50);
    } // update_mbid

    /**
     * update_license
     * updates license field
     * @param string $new_license
     * @param integer $song_id
     */
    public static function update_license($new_license, $song_id)
    {
        self::_update_item('license', $new_license, $song_id, 50, true);
    } // update_license

    /**
     * update_artist
     * updates the artist field
     * @param int $new_artist
     * @param int $song_id
     * @param int $old_artist
     * @param bool $update_counts
     * @return bool
     */
    public static function update_artist($new_artist, $song_id, $old_artist, $update_counts = true)
    {
        if (self::_update_item('artist', $new_artist, $song_id, 50) !== false) {
            self::migrate_artist($new_artist, $song_id, $old_artist);
            if ($update_counts) {
                Artist::update_table_counts();
            }

            return true;
        }

        return false;
    } // update_artist

    /**
     * update_album
     * updates the album field
     * @param int $new_album
     * @param int $song_id
     * @param int $old_album
     * @param bool $update_counts
     * @return bool
     */
    public static function update_album($new_album, $song_id, $old_album, $update_counts = true)
    {
        if (self::_update_item('album', $new_album, $song_id, 50, true) !== false) {
            self::migrate_album($new_album, $song_id, $old_album);
            if ($update_counts) {
                Album::update_table_counts();
            }

            return true;
        }

        return false;
    } // update_album

    /**
     * update_utime
     * sets a new update time
     * @param integer $song_id
     * @param integer $time
     */
    public static function update_utime($song_id, $time = 0)
    {
        if (!$time) {
            $time = time();
        }

        self::_update_item('update_time', $time, $song_id, 75, true);
    } // update_utime

    /**
     * update_played
     * sets the played flag
     * @param boolean $new_played
     * @param integer $song_id
     */
    public static function update_played($new_played, $song_id)
    {
        self::_update_item('played', ($new_played ? 1 : 0), $song_id, 25);
    } // update_played

    /**
     * update_enabled
     * sets the enabled flag
     * @param boolean $new_enabled
     * @param integer $song_id
     */
    public static function update_enabled($new_enabled, $song_id)
    {
        self::_update_item('enabled', ($new_enabled ? 1 : 0), $song_id, 75, true);
    } // update_enabled

    /**
     * _update_item
     * This is a private function that should only be called from within the song class.
     * It takes a field, value song id and level. first and foremost it checks the level
     * against Core::get_global('user') to make sure they are allowed to update this record
     * it then updates it and sets $this->{$field} to the new value
     * @param string $field
     * @param string|int $value
     * @param int $song_id
     * @param int $level
     * @param boolean $check_owner
     * @return PDOStatement|boolean
     */
    private static function _update_item($field, $value, $song_id, $level, $check_owner = false)
    {
        if ($check_owner && !empty(Core::get_global('user'))) {
            $item = new Song($song_id);
            if (isset($item->id) && $item->get_user_owner() == Core::get_global('user')->id) {
                $level = 25;
            }
        }
        /* Check them Rights! */
        if (!Access::check('interface', $level)) {
            return false;
        }

        /* Can't update to blank */
        if (!strlen(trim((string)$value)) && $field != 'comment') {
            return false;
        }

        $sql = "UPDATE `song` SET `$field` = ? WHERE `id` = ?";

        return Dba::write($sql, array($value, $song_id));
    } // _update_item

    /**
     * _update_ext_item
     * This updates a song record that is housed in the song_ext_info table
     * These are items that aren't used normally, and often large/informational only
     * @param string $field
     * @param string $value
     * @param integer $song_id
     * @param integer $level
     * @param boolean $check_owner
     * @return PDOStatement|boolean
     */
    private static function _update_ext_item($field, $value, $song_id, $level, $check_owner = false)
    {
        if ($check_owner) {
            $item = new Song($song_id);
            if ($item->id && $item->get_user_owner() == Core::get_global('user')->id) {
                $level = 25;
            }
        }

        /* Check them rights boy! */
        if (!Access::check('interface', $level)) {
            return false;
        }

        $sql = "UPDATE `song_data` SET `$field` = ? WHERE `song_id` = ?";

        return Dba::write($sql, array($value, $song_id));
    } // _update_ext_item

    /**
     * format
     * This takes the current song object
     * and does a ton of formatting on it creating f_??? variables on the current
     * object
     * @param boolean $details
     */
    public function format($details = true)
    {
        if ($details) {
            $this->fill_ext_info();

            // Get the top tags
            $this->tags   = Tag::get_top_tags('song', $this->id);
            $this->f_tags = Tag::get_display($this->tags, true, 'song');
        }

        if (!isset($this->artists)) {
            $this->get_artists();
        }
        if (!isset($this->albumartists)) {
            $this->albumartists = self::get_parent_array($this->album, 'album');
        }
        $this->albumartist = Album::get_album_artist($this->album);

        // Format the album name
        $this->f_album_full = $this->get_album_fullname();
        $this->f_album      = $this->f_album_full;

        // Format the artist name
        $this->f_artist_full = $this->get_artist_fullname();
        $this->f_artist      = $this->f_artist_full;

        // Format the album_artist name
        $this->f_albumartist_full = $this->get_album_artist_fullname();

        // Format the title
        $this->f_name_full = $this->get_fullname();

        // Create Links for the different objects
        $this->get_f_link();
        $this->get_f_artist_link();
        $this->get_f_albumartist_link();
        $this->get_f_album_link();

        // Format the Bitrate
        $this->f_bitrate = (int)($this->bitrate / 1024) . "-" . strtoupper((string)$this->mode);

        // Format the Time
        $min            = floor($this->time / 60);
        $sec            = sprintf("%02d", ($this->time % 60));
        $this->f_time   = $min . ":" . $sec;
        $hour           = sprintf("%02d", floor($min / 60));
        $min_h          = sprintf("%02d", ($min % 60));
        $this->f_time_h = $hour . ":" . $min_h . ":" . $sec;

        // Format the track (there isn't really anything to do here)
        $this->f_track = (string)$this->track;

        // Format the size
        $this->f_size = Ui::format_bytes($this->size);

        $web_path       = AmpConfig::get('web_path');
        $this->f_lyrics = "<a title=\"" . scrub_out($this->title) . "\" href=\"" . $web_path . "/song.php?action=show_lyrics&song_id=" . $this->id . "\">" . T_('Show Lyrics') . "</a>";

        $this->f_file = $this->f_artist . ' - ';
        if ($this->track) {
            $this->f_file .= $this->track . ' - ';
        }
        $this->f_file .= $this->get_fullname() . '.' . $this->type;

        $this->f_publisher = $this->label;
        $this->f_composer  = $this->composer;

        $year              = (int)$this->year;
        $this->f_year_link = "<a href=\"" . $web_path . "/search.php?type=album&action=search&limit=0&rule_1=year&rule_1_operator=2&rule_1_input=" . $year . "\">" . $year . "</a>";

        if (AmpConfig::get('licensing') && $this->license !== null) {
            $license = new License($this->license);

            $this->f_license = $license->getLinkFormatted();
        }
    } // format

    /**
     * does the item have art?
     * @return bool
     */
    public function has_art()
    {
        if (!isset($this->has_art)) {
            $this->has_art = (AmpConfig::get('show_song_art', false) && Art::has_db($this->id, 'song') || Art::has_db($this->album, 'album'));
        }

        return $this->has_art;
    }

    /**
     * Get item keywords for metadata searches.
     * @return array
     */
    public function get_keywords()
    {
        $keywords               = array();
        $keywords['mb_trackid'] = array(
            'important' => false,
            'label' => T_('Track MusicBrainzID'),
            'value' => $this->mbid
        );
        $keywords['artist'] = array(
            'important' => true,
            'label' => T_('Artist'),
            'value' => $this->f_artist
        );
        $keywords['title'] = array(
            'important' => true,
            'label' => T_('Title'),
            'value' => $this->get_fullname()
        );

        return $keywords;
    }

    /**
     * Get total count
     * @return integer
     */
    public function get_totalcount()
    {
        return $this->total_count;
    }

    /**
     * Get item fullname.
     * @return string
     */
    public function get_fullname()
    {
        if (!isset($this->f_name)) {
            $this->f_name = $this->title;
        }

        return $this->f_name;
    }

    /**
     * Get item link.
     * @return string
     */
    public function get_link()
    {
        // don't do anything if it's formatted
        if (!isset($this->link)) {
            $web_path   = AmpConfig::get('web_path');
            $this->link = $web_path . "/song.php?action=show_song&song_id=" . $this->id;
        }

        return $this->link;
    }

    /**
     * Get item f_link.
     * @return string
     */
    public function get_f_link()
    {
        // don't do anything if it's formatted
        if (!isset($this->f_link)) {
            $this->f_link = "<a href=\"" . scrub_out($this->get_link()) . "\" title=\"" . scrub_out($this->get_artist_fullname()) . " - " . scrub_out($this->get_fullname()) . "\"> " . scrub_out($this->get_fullname()) . "</a>";
        }

        return $this->f_link;
    }

    /**
     * Get item album_artists array
     * @return array
     */
    public function get_artists()
    {
        if (!isset($this->artists)) {
            $this->artists = self::get_parent_array($this->id);
        }

        return $this->artists;
    }

    /**
     * Get item f_artist_link.
     * @return string
     */
    public function get_f_artist_link()
    {
        // don't do anything if it's formatted
        if (!isset($this->f_artist_link)) {
            $this->f_artist_link  = '';
            $web_path             = AmpConfig::get('web_path');
            if (!isset($this->artists)) {
                $this->get_artists();
            }
            foreach ($this->artists as $artist_id) {
                $artist_fullname = scrub_out($this->get_artist_fullname($artist_id));
                $this->f_artist_link .= "<a href=\"" . $web_path . "/artists.php?action=show&artist=" . $artist_id . "\" title=\"" . $artist_fullname . "\">" . $artist_fullname . "</a>,&nbsp";
            }
            $this->f_artist_link = rtrim($this->f_artist_link, ",&nbsp");
        }

        return $this->f_artist_link;
    }

    /**
     * Get item f_albumartist_link.
     * @return string
     */
    public function get_f_albumartist_link()
    {
        // don't do anything if it's formatted
        if (!isset($this->f_albumartist_link)) {
            $this->f_albumartist_link = '';
            $web_path                 = AmpConfig::get('web_path');
            if (!isset($this->albumartists)) {
                $this->albumartists = self::get_parent_array($this->album, 'album');
            }
            foreach ($this->albumartists as $artist_id) {
                $artist_fullname = scrub_out(Artist::get_fullname_by_id($artist_id));
                $this->f_albumartist_link .= "<a href=\"" . $web_path . '/artists.php?action=show&artist=' . $artist_id . "\" title=\"" . $artist_fullname . "\">" . $artist_fullname . "</a>,&nbsp";
            }
            $this->f_albumartist_link = rtrim($this->f_albumartist_link, ",&nbsp");
        }

        return $this->f_albumartist_link;
    }

    /**
     * Get item get_f_album_link.
     * @return string
     */
    public function get_f_album_link()
    {
        // don't do anything if it's formatted
        if (!isset($this->f_album_link)) {
            $this->f_album_link = '';
            $web_path           = AmpConfig::get('web_path');
            $this->f_album_link = "<a href=\"" . $web_path . "/albums.php?action=show&amp;album=" . $this->album . "\" title=\"" . scrub_out($this->get_album_fullname()) . "\"> " . scrub_out($this->get_album_fullname()) . "</a>";
        }

        return $this->f_album_link;
    }

    /**
     * Get item get_f_album_disk_link.
     * @return string
     */
    public function get_f_album_disk_link()
    {
        // don't do anything if it's formatted
        if (!isset($this->f_album_disk_link)) {
            $this->f_album_disk_link = '';
            $web_path                = AmpConfig::get('web_path');
            $this->f_album_disk_link = "<a href=\"" . $web_path . "/albums.php?action=show_disk&amp;album_disk=" . $this->get_album_disk() . "\" title=\"" . scrub_out($this->get_album_disk_fullname()) . "\"> " . scrub_out($this->get_album_disk_fullname()) . "</a>";
        }

        return $this->f_album_disk_link;
    }

    /**
     * Get parent item description.
     * @return array|null
     */
    public function get_parent()
    {
        return array('object_type' => 'album', 'object_id' => $this->album);
    }

    /**
     * Get parent song artists.
     * @param int $object_id
     * @return array
     */
    public static function get_parent_array($object_id, $type = 'artist')
    {
        $results = array();
        $sql     = ($type == 'album')
            ? "SELECT DISTINCT `object_id` FROM `album_map` WHERE `object_type` = 'album' AND `album_id` = ?;"
            : "SELECT DISTINCT `artist_id` AS `object_id` FROM `artist_map` WHERE `object_type` = 'song' AND `object_id` = ?;";
        $db_results = Dba::read($sql, array($object_id));

        while ($row = Dba::fetch_assoc($db_results)) {
            $results[] = $row['object_id'];
        }

        return $results;
    }

    /**
     * Get item children.
     * @return array
     */
    public function get_childrens()
    {
        return array();
    }

    /**
     * Search for direct children of an object
     * @param string $name
     * @return array
     */
    public function get_children($name)
    {
        debug_event(self::class, 'get_children ' . $name, 5);

        return array();
    }

    /**
     * Get all childrens and sub-childrens medias.
     * @param string $filter_type
     * @return array
     */
    public function get_medias($filter_type = null)
    {
        $medias = array();
        if ($filter_type === null || $filter_type == 'song') {
            $medias[] = array(
                'object_type' => 'song',
                'object_id' => $this->id
            );
        }

        return $medias;
    }

    /**
     * get_catalogs
     *
     * Get all catalog ids related to this item.
     * @return integer[]
     */
    public function get_catalogs()
    {
        return array($this->catalog);
    }

    /**
     * Get item's owner.
     * @return integer|null
     */
    public function get_user_owner()
    {
        if ($this->user_upload !== null) {
            return $this->user_upload;
        }

        return null;
    }

    /**
     * Get default art kind for this item.
     * @return string
     */
    public function get_default_art_kind()
    {
        return 'default';
    }

    /**
     * get_description
     * @return string
     */
    public function get_description()
    {
        if (!empty($this->comment)) {
            return $this->comment;
        }

        $album = new Album($this->album);
        $album->format();

        return $album->get_description();
    }

    /**
     * display_art
     * @param integer $thumb
     * @param boolean $force
     */
    public function display_art($thumb = 2, $force = false)
    {
        $object_id = null;
        $type      = null;

        if (Art::has_db($this->id, 'song')) {
            $object_id = $this->id;
            $type      = 'song';
        } else {
            if (Art::has_db($this->album, 'album')) {
                $object_id = $this->album;
                $type      = 'album';
            } else {
                if (Art::has_db($this->artist, 'artist') || $force) {
                    $object_id = $this->artist;
                    $type      = 'artist';
                }
            }
        }

        if ($object_id !== null && $type !== null) {
            Art::display($type, $object_id, $this->get_fullname(), $thumb, $this->get_link());
        }
    }

    /**
     * get_fields
     * This returns all of the 'data' fields for this object, we need to filter out some that we don't
     * want to present to a user, and add some that don't exist directly on the object but are related
     * @return array
     */
    public static function get_fields()
    {
        $fields = get_class_vars(Song::class);

        unset($fields['id'], $fields['_transcoded'], $fields['_fake'], $fields['cache_hit'], $fields['mime'], $fields['type']);

        // Some additional fields
        $fields['tag']     = true;
        $fields['catalog'] = true;
        // FIXME: These are here to keep the ideas, don't want to have to worry about them for now
        // $fields['rating'] = true;
        // $fields['recently Played'] = true;

        return $fields;
    } // get_fields

    /**
     * get_from_path
     * This returns all of the songs that exist under the specified path
     * @param string $path
     * @return integer[]
     */
    public static function get_from_path($path)
    {
        $path = Dba::escape($path);

        $sql        = "SELECT * FROM `song` WHERE `file` LIKE '$path%'";
        $db_results = Dba::read($sql);

        $songs = array();

        while ($row = Dba::fetch_assoc($db_results)) {
            $songs[] = (int)$row['id'];
        }

        return $songs;
    } // get_from_path

    /**
     * @function    get_rel_path
     * @discussion    returns the path of the song file stripped of the catalog path
     *        used for mpd playback
     * @param string $file_path
     * @param integer $catalog_id
     * @return string
     */
    public function get_rel_path($file_path = null, $catalog_id = 0)
    {
        $info = null;
        if ($file_path === null) {
            $info      = $this->has_info();
            $file_path = $info['file'];
        }
        if (!$catalog_id) {
            if (!is_array($info)) {
                $info = $this->has_info();
            }
            $catalog_id = $info['catalog'];
        }
        $catalog = Catalog::create_from_id($catalog_id);

        return $catalog->get_rel_path($file_path);
    } // get_rel_path

    /**
     * play_url
     * This function takes all the song information and correctly formats a
     * a stream URL taking into account the downsampling mojo and everything
     * else, this is the true function
     * @param string $additional_params
     * @param string $player
     * @param boolean $local
     * @param int|string $uid
     * @return string
     */
    public function play_url($additional_params = '', $player = '', $local = false, $uid = false, $streamToken = false)
    {
        if (!$this->id) {
            return '';
        }
        if (!$uid) {
            // No user in the case of upnp. Set to 0 instead. required to fix database insertion errors
            $uid = Core::get_global('user')->id ?? 0;
        }
        // set no use when using auth
        if (!AmpConfig::get('use_auth') && !AmpConfig::get('require_session')) {
            $uid = -1;
        }
        // if you transcode the media mime will change
        if (empty($additional_params) || !strpos($additional_params, 'action=download')) {
            $cache_path     = (string)AmpConfig::get('cache_path', '');
            $cache_target   = (string)AmpConfig::get('cache_target', '');
            $file_target    = Catalog::get_cache_path($this->id, $this->catalog, $cache_path, $cache_target);
            $transcode_type = ($file_target && is_file($file_target))
                ? $cache_target
                : Stream::get_transcode_format($this->type, null, $player);
            if ($this->type !== $transcode_type) {
                $this->type    = $transcode_type;
                $this->mime    = self::type_to_mime($this->type);
                $this->bitrate = ((int)AmpConfig::get('transcode_bitrate', 128)) * 1000;
                $additional_params .= (!empty($additional_params))
                    ? '&transcode_to=' . $transcode_type
                    : 'transcode_to=' . $transcode_type;
            }
        }

        $media_name = $this->get_stream_name() . "." . $this->type;
        $media_name = preg_replace("/[^a-zA-Z0-9\. ]+/", "-", $media_name);
        $media_name = (AmpConfig::get('stream_beautiful_url'))
            ? urlencode($media_name)
            : rawurlencode($media_name);

        $url = Stream::get_base_url($local, $streamToken) . "type=song&oid=" . $this->id . "&uid=" . (string) $uid . $additional_params;
        if ($player !== '') {
            $url .= "&player=" . $player;
        }
        $url .= "&name=" . $media_name;

        return Stream_Url::format($url);
    }

    /**
     * Get stream name.
     * @return string
     */
    public function get_stream_name()
    {
        return $this->get_artist_fullname() . " - " . $this->title;
    }

    /**
     * Get stream types.
     * @param string $player
     * @return array
     */
    public function get_stream_types($player = null)
    {
        return Stream::get_stream_types_for_type($this->type, $player);
    }

    /**
     * Get transcode settings.
     * @param string $target
     * @param string $player
     * @param array $options
     * @return array
     */
    public function get_transcode_settings($target = null, $player = null, $options = array())
    {
        return Stream::get_transcode_settings_for_media($this->type, $target, $player, 'song', $options);
    }

    /**
     * Get lyrics.
     * @return array
     */
    public function get_lyrics()
    {
        if ($this->lyrics) {
            return array('text' => $this->lyrics);
        }

        foreach (Plugin::get_plugins('get_lyrics') as $plugin_name) {
            $plugin = new Plugin($plugin_name);
            if ($plugin->load(Core::get_global('user'))) {
                $lyrics = $plugin->_plugin->get_lyrics($this);
                if ($lyrics != false) {
                    return $lyrics;
                }
            }
        }

        return null;
    }

    /**
     * Run custom play action.
     * @param integer $action_index
     * @param string $codec
     * @return array
     */
    public function run_custom_play_action($action_index, $codec = '')
    {
        $transcoder = array();
        $actions    = self::get_custom_play_actions();
        if ($action_index <= count($actions)) {
            $action = $actions[$action_index - 1];
            if (!$codec) {
                $codec = $this->type;
            }

            $run = str_replace("%f", $this->file, $action['run']);
            $run = str_replace("%c", $codec, $run);
            $run = str_replace("%a", $this->f_artist, $run);
            $run = str_replace("%A", $this->f_album, $run);
            $run = str_replace("%t", $this->get_fullname(), $run);

            debug_event(self::class, "Running custom play action: " . $run, 3);

            $descriptors = array(1 => array('pipe', 'w'));
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                // Windows doesn't like to provide stderr as a pipe
                $descriptors[2] = array('pipe', 'w');
            }
            $process = proc_open($run, $descriptors, $pipes);

            $transcoder['process'] = $process;
            $transcoder['handle']  = $pipes[1];
            $transcoder['stderr']  = $pipes[2];
            $transcoder['format']  = $codec;
        }

        return $transcoder;
    }

    /**
     * Get custom play actions.
     * @return array
     */
    public static function get_custom_play_actions()
    {
        $actions = array();
        $count   = 0;
        while (AmpConfig::get('custom_play_action_title_' . $count)) {
            $actions[] = array(
                'index' => ($count + 1),
                'title' => AmpConfig::get('custom_play_action_title_' . $count),
                'icon' => AmpConfig::get('custom_play_action_icon_' . $count),
                'run' => AmpConfig::get('custom_play_action_run_' . $count),
            );
            ++$count;
        }

        return $actions;
    }

    /**
     * Update Metadata from array
     * @param array $meta_value
     */
    public function updateMetadata($meta_value)
    {
        foreach ($meta_value as $metadataId => $value) {
            $metadata = $this->metadataRepository->findById($metadataId);
            if (!$metadata || $value != $metadata->getData()) {
                $metadata->setData($value);
                $this->metadataRepository->update($metadata);
            }
        }
    }

    /**
     * get_deleted
     * get items from the deleted_songs table
     * @return int[]
     */
    public static function get_deleted()
    {
        $deleted    = array();
        $sql        = "SELECT * FROM `deleted_song`";
        $db_results = Dba::read($sql);
        while ($row = Dba::fetch_assoc($db_results)) {
            $deleted[] = $row;
        }

        return $deleted;
    } // get_deleted

    /**
     * Migrate an artist data to a new object
     * @param int $new_artist
     * @param int $song_id
     * @param int $old_artist
     */
    public static function migrate_artist($new_artist, $song_id, $old_artist)
    {
        // migrate stats for the old artist
        Stats::migrate('artist', $old_artist, $new_artist, $song_id);
        Useractivity::migrate('artist', $old_artist, $new_artist);
        Recommendation::migrate('artist', $old_artist);
        Share::migrate('artist', $old_artist, $new_artist);
        Shoutbox::migrate('artist', $old_artist, $new_artist);
        Tag::migrate('artist', $old_artist, $new_artist);
        Userflag::migrate('artist', $old_artist, $new_artist);
        Rating::migrate('artist', $old_artist, $new_artist);
        Art::duplicate('artist', $old_artist, $new_artist);
        Wanted::migrate('artist', $old_artist, $new_artist);
        Catalog::migrate_map('artist', $old_artist, $new_artist);
        // update mapping tables
        $sql = "UPDATE IGNORE `album_map` SET `object_id` = ? WHERE `object_id` = ?";
        if (Dba::write($sql, array($new_artist, $old_artist)) === false) {
            return false;
        }
        $sql = "UPDATE IGNORE `artist_map` SET `artist_id` = ? WHERE `artist_id` = ?";
        if (Dba::write($sql, array($new_artist, $old_artist)) === false) {
            return false;
        }
        $sql = "UPDATE IGNORE `catalog_map` SET `object_id` = ? WHERE `object_type` = ? AND `object_id` = ?";
        if (Dba::write($sql, array($new_artist, 'artist', $old_artist)) === false) {
            return false;
        }
        // delete leftovers duplicate maps
        $sql = "DELETE FROM `album_map` WHERE `object_id` = ?";
        Dba::write($sql, array($old_artist));
        $sql = "DELETE FROM `artist_map` WHERE `artist_id` = ?";
        Dba::write($sql, array($old_artist));
        $sql = "DELETE FROM `catalog_map` WHERE `object_type` = ? AND `object_id` = ?";
        Dba::write($sql, array('artist', $old_artist));

        return true;
    }

    /**
     * Migrate an album data to a new object
     * @param int $new_album
     * @param int $song_id
     * @param int $old_album
     */
    public static function migrate_album($new_album, $song_id, $old_album)
    {
        // migrate stats for the old album
        Stats::migrate('album', $old_album, $new_album, $song_id);
        Useractivity::migrate('album', $old_album, $new_album);
        //Recommendation::migrate('album', $old_album);
        Share::migrate('album', $old_album, $new_album);
        Shoutbox::migrate('album', $old_album, $new_album);
        Tag::migrate('album', $old_album, $new_album);
        Userflag::migrate('album', $old_album, $new_album);
        Rating::migrate('album', $old_album, $new_album);
        Art::duplicate('album', $old_album, $new_album);
        Catalog::migrate_map('album', $old_album, $new_album);

        // update mapping tables
        $sql = "UPDATE IGNORE `album_disk` SET `album_id` = ? WHERE `album_id` = ?";
        if (Dba::write($sql, array($new_album, $old_album)) === false) {
            return false;
        }
        $sql = "UPDATE IGNORE `album_map` SET `album_id` = ? WHERE `album_id` = ? AND `object_id` = ? AND `object_type` = 'song'";
        if (Dba::write($sql, array($new_album, $old_album, $song_id)) === false) {
            return false;
        }
        $sql = "UPDATE IGNORE `artist_map` SET `object_id` = ? WHERE `object_type` = ? AND `object_id` = ?";
        if (Dba::write($sql, array($new_album, 'album', $old_album)) === false) {
            return false;
        }
        $sql = "UPDATE IGNORE `catalog_map` SET `object_id` = ? WHERE `object_type` = ? AND `object_id` = ?";
        if (Dba::write($sql, array($new_album, 'album', $old_album)) === false) {
            return false;
        }
        // delete leftovers duplicate maps
        $sql = "DELETE FROM `album_disk` WHERE `album_id` = ?";
        Dba::write($sql, array($old_album));
        $sql = "DELETE FROM `album_map` WHERE `album_id` = ?";
        Dba::write($sql, array($old_album));
        $sql = "DELETE FROM `artist_map` WHERE `object_type` = ? AND `object_id` = ?";
        Dba::write($sql, array('album', $old_album));
        $sql = "DELETE FROM `catalog_map` WHERE `object_type` = ? AND `object_id` = ?";
        Dba::write($sql, array('album', $old_album));

        return true;
    }

    /**
     * remove
     * Delete the object from disk and/or database where applicable.
     * @return bool
     */
    public function remove(): bool
    {
        return $this->getSongDeleter()->delete($this);
    }

    /**
     * @deprecated
     */
    private function getSongTagWriter(): SongTagWriterInterface
    {
        global $dic;

        return $dic->get(SongTagWriterInterface::class);
    }

    /**
     * @deprecated
     */
    private function getSongDeleter(): SongDeleterInterface
    {
        global $dic;

        return $dic->get(SongDeleterInterface::class);
    }

    /**
     * @deprecated inject dependency
     */
    private static function getUserActivityPoster(): UserActivityPosterInterface
    {
        global $dic;

        return $dic->get(UserActivityPosterInterface::class);
    }
}
