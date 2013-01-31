<?php

/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Media;

//class for scanning directories for music
class Scanner {
	/**
	 * @var Extractor $extractor
	 */
	private $extractor;

	/**
	 * @var Collection $collection
	 */
	private $collection;

	/**
	 * @param Collection $collection
	 */
	public function __construct($collection) {
		$this->collection = $collection;
		$this->extractor = new Extractor_GetID3();
	}

	/**
	 * get a list of all music files of the user
	 *
	 * @return array
	 */
	public function getMusic() {
		$music = \OC_Files::searchByMime('audio');
		$ogg = \OC_Files::searchByMime('application/ogg');
		return array_merge($music, $ogg);
	}
	
	/**
	 * get a list of all video files of the user
	 *
	 * @return array
	 */
	public function getVideo() {
		$video = \OC_Files::searchByMime('video');
		return $video;
	}


	/**
	 * scan all media files for the current user
	 *
	 * @return int the number of songs found
	 */
	public function scanCollection() {
		$music = $this->getMusic();
		$video = $this->getVideo();
		\OC_Hook::emit('media', 'song_count', array('count' => count($music)));
		$songs = 0;
		foreach ($music as $file) {
			$this->scanFile($file);
			$songs++;
			\OC_Hook::emit('media', 'song_scanned', array('path' => $file, 'count' => $songs));
		}		
		foreach ($video as $file) {
			$this->scanFile($file);
		}
		return $songs;
	}

	/**
	 * scan a file for music
	 *
	 * @param string $path
	 * @return boolean
	 */
	public function scanFile($path) {
		$data = $this->extractor->extract($path);
		
		if($data['type'] == 'song') {
			$artistId = $this->collection->addArtist($data['artist']);
			$albumId = $this->collection->addAlbum($data['album'], $artistId);

			$this->collection->addSong($data['title'], $path, $artistId, $albumId, $data['length'], $data['track'], $data['size']);
		}
		elseif($data['type'] == 'video') {
			$this->collection->addVideo($data['name'], $path, $data['mime'], $data['resolution_x'], $data['resolution_y'], $data['size']);
		}
		
		return true;
	}
}
