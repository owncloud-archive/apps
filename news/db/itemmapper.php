<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;

/**
 * This class maps an item to a row of the items table in the database.
 * It follows the Data Mapper pattern (see http://martinfowler.com/eaaCatalog/dataMapper.html).
 */
class ItemMapper {

	const tableName = '*PREFIX*news_items';
	private $userid;

	public function __construct($userid = null) {
		if ($userid !== null) {
			$this->userid = $userid;
		}
		else {
			$this->userid = \OCP\USER::getUser();
		}
	}

	/**
	 * @brief
	 * @param row a row from the items table of the database
	 * @returns an object of the class OC_News_Item
	 */
	public function fromRow($row) {
		$url = $row['url'];
		$title = $row['title'];
		$guid = $row['guid'];
		$body = $row['body'];
		$id = $row['id'];
		
		$item = new Item($url, $title, $guid, $body, $id);
		$item->setStatus($row['status']);
		$item->setAuthor($row['author']);
		$item->setFeedId($row['feed_id']);
		$item->setDate(Utils::dbtimestampToUnixtime($row['pub_date']));

		$feedmapper = new FeedMapper($this->userid);
		$feed = $feedmapper->findById($row['feed_id']);
		$item->setFeedTitle($feed->getTitle());

		if($row['enclosure_mime'] !== null && $row['enclosure_link'] !== null) {
			$enclosure = new Enclosure();
			$enclosure->setMimeType($row['enclosure_mime']);
			$enclosure->setLink($row['enclosure_link']);
			$item->setEnclosure($enclosure);
		}
		
		return $item;
	}

	/**
	 * @brief Retrieve all the item corresponding to a feed from the database
	 * @param feedid The id of the feed in the database table.
	 */
	public function findByFeedId($feedid) {
		$stmt = \OCP\DB::prepare('SELECT * FROM ' . self::tableName . ' WHERE feed_id = ? ORDER BY pub_date DESC');
		$result = $stmt->execute(array($feedid));
		
		$items = array();
		while ($row = $result->fetchRow()) {
			$item = $this->fromRow($row);
			$items[] = $item;
		}

		return $items;
	}


	/**
	 * @brief Retrieve all the items corresponding to a feed from the database with a particular status
	 * @param feedid The id of the feed in the database table.
	 * @param status one of the constants defined in OCA\News\StatusFlag
	 */
	public function findAllStatus($feedid, $status) {
		$stmt = \OCP\DB::prepare('SELECT * FROM ' . self::tableName . '
				WHERE feed_id = ?
				AND ((status & ?) > 0)
				ORDER BY pub_date DESC');
		$result = $stmt->execute(array($feedid, $status));

		$items = array();
		while ($row = $result->fetchRow()) {
			$item = $this->fromRow($row);
			$items[] = $item;
		}

		return $items;
	}

	/*
	 * @brief Retrieve all the items from the database with a particular status
	 * @param status one of the constants defined in OCA\News\StatusFlag
	 */
	public function findEveryItemByStatus($status) {
		$stmt = \OCP\DB::prepare('SELECT ' . self::tableName . '.* FROM ' . self::tableName . '
				JOIN '. FeedMapper::tableName .' ON
				'. FeedMapper::tableName .'.id = ' . self::tableName . '.feed_id
				WHERE '. FeedMapper::tableName .'.user_id = ?
				AND ((' . self::tableName . '.status & ?) > 0)
				ORDER BY ' . self::tableName . '.pub_date DESC');
		$result = $stmt->execute(array($this->userid, $status));

		$items = array();
		while ($row = $result->fetchRow()) {
			$item = $this->fromRow($row);
			$items[] = $item;
		}

		return $items;
	}

	public function countAllStatus($feedid, $status) {
		$stmt = \OCP\DB::prepare('SELECT COUNT(*) as size FROM ' . self::tableName . '
				WHERE feed_id = ?
				AND ((status & ?) > 0)');
		$result=$stmt->execute(array($feedid, $status))->fetchRow();
		return $result['size'];
	}

	/**
	 * @brief Count all the items from the database with a particular status
	 * @param status one of the constants defined in OCA\News\StatusFlag
	 */
	public function countEveryItemByStatus($status) {
		$stmt = \OCP\DB::prepare('SELECT COUNT(*) as size FROM ' . self::tableName . '
				JOIN '. FeedMapper::tableName .' ON
				'. FeedMapper::tableName .'.id = ' . self::tableName . '.feed_id
				WHERE '. FeedMapper::tableName .'.user_id = ?
				AND ((' . self::tableName . '.status & ?) > 0)');
		$result = $stmt->execute(array($this->userid, $status))->fetchRow();;

		return $result['size'];
	}

	public function findIdFromGuid($guid_hash, $guid, $feedid) {
		$stmt = \OCP\DB::prepare('
				SELECT * FROM ' . self::tableName . '
				WHERE guid_hash = ?
				AND feed_id = ?
				');
		$result = $stmt->execute(array($guid_hash, $feedid));
		//TODO: if there is more than one row, falling back to comparing $guid
		$row = $result->fetchRow();
		$id = null;
		if ($row != null) {
			$id = $row['id'];
		}
		return $id;
	}


	/**
	 * @brief marks all items read
	 * @param int $feedId: the id of the feed
	 * @param int $mostRecentItemId: every item with the same or lower id will 
	 *								 be marked read
	 */
	public function markAllRead($feedId, $mostRecentItemId){
		if($mostRecentItemId === 0){
			$stmt = \OCP\DB::prepare('
				UPDATE ' . self::tableName .
				' SET status = status & ?
				WHERE 
					feed_id = ?');
		
			$params = array(
				~StatusFlag::UNREAD,
				$feedId
			);
		} else {
			$stmt = \OCP\DB::prepare('
				UPDATE ' . self::tableName .
				' SET status = status & ?
				WHERE 
					feed_id = ?
					AND
					id <= ?');
		
			$params = array(
				~StatusFlag::UNREAD,
				$feedId,
				$mostRecentItemId
			);
		}
		
		$stmt->execute($params);
	}


	/**
	 * @brief Update the item after its status has changed
	 * @returns The item whose status has changed.
	 */
	public function update(Item $item) {

		$itemid = $item->getId();
		$status = $item->getStatus();

		$stmt = \OCP\DB::prepare('
				UPDATE ' . self::tableName .
				' SET status = ?
				WHERE id = ?
				');

		$params=array(
			$status,
			$itemid
			);
			
		$result = $stmt->execute($params);

		
		return true;
	}

	/**
	 * @brief Save the feed and all its items into the database
	 * @returns The id of the feed in the database table.
	 */
	public function save(Item $item, $feedid) {
		$guid = $item->getGuid();
		$guid_hash = md5($guid);

		$status = $item->getStatus();

		$itemid =  $this->findIdFromGuid($guid_hash, $guid, $feedid);

		if ($itemid == null) {
			$title = $item->getTitle();
			$body = $item->getBody();
			$author = $item->getAuthor();
			$enclosure_mime = null;
			$enclosure_link = null;
			
			if($enclosure = $item->getEnclosure()) {
				$enclosure_mime = $enclosure->getMimeType();
				$enclosure_link = $enclosure->getLink();
			}
			
			$stmt = \OCP\DB::prepare('
				INSERT INTO ' . self::tableName .
				'(url, title, body, author, guid, guid_hash, pub_date, enclosure_mime, enclosure_link, feed_id, status)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				');

			if(empty($title)) {
				$l = \OC_L10N::get('news');
				$title = $l->t('no title');
			}

			if(empty($body)) {
				$l = \OC_L10N::get('news');
				$body = $l->t('no body');
			}

			$pub_date = Utils::unixtimeToDbtimestamp($item->getDate());

			$params=array(
				$item->getUrl(),
				$title,
				$body,
				$author,
				$guid,
				$guid_hash,
				$pub_date,
				$enclosure_mime,
				$enclosure_link,
				$feedid,
				$status
			);

			$stmt->execute($params);

			$itemid = \OCP\DB::insertid(self::tableName);
		}
		else {
			$this->update($item);
		}
		$item->setId($itemid);
		return $itemid;
	}

	/**
	 * @brief Retrieve an item from the database
	 * @param id The id of the item in the database table.
	 */
	public function findById($id) {

		$stmt = \OCP\DB::prepare('SELECT ' . self::tableName . '.id AS id, ' . self::tableName . 
			'.url AS url, ' . self::tableName . '.title AS title, guid, body, status, author, feed_id, pub_date, enclosure_mime, enclosure_link' .
			' FROM ' . self::tableName . ' JOIN ' . FeedMapper::tableName . 
			' ON ' . self::tableName . '.feed_id = ' . FeedMapper::tableName . '.id WHERE (' . self::tableName . 
			'.id = ? AND ' . FeedMapper::tableName . '.user_id = ? )');
		$result = $stmt->execute(array($id, $this->userid));
		
		/*
		$stmt = \OCP\DB::prepare('SELECT * FROM ' . self::tableName . ' WHERE id = ?');
		$result = $stmt->execute(array($id));
		*/
		$row = $result->fetchRow();

		$item = $this->fromRow($row);

		return $item;

	}


	/**
	 * @brief Permanently delete all items belonging to a feed from the database
	 * @param feedid The id of the feed that we wish to delete
	 * @return
	 */
	public function deleteAll($feedid) {
		if ($feedid == null) {
			return false;
		}
		$stmt = \OCP\DB::prepare('DELETE FROM ' . self::tableName .' WHERE feed_id = ?');

		$result = $stmt->execute(array($feedid));

		return $result;
	}

	/**
	 * Returns the unread count
	 * @param $feedType the type of the feed
	 * @param $feedId the id of the feed or folder
	 * @return the unread count
	 */
	public function getUnreadCount($feedType, $feedId){
		$unreadCount = 0;

		switch ($feedType) {
			case FeedType::STARRED:
				$unreadCount = $this->countEveryItemByStatus(StatusFlag::IMPORTANT);
				break;

			case FeedType::SUBSCRIPTIONS:
				$unreadCount = $this->countEveryItemByStatus(StatusFlag::UNREAD);
				break;

			case FeedType::FOLDER:
				$feedMapper = new FeedMapper($this->userId);
				$feeds = $feedMapper->findByFolderId($feedId);
				foreach($feeds as $feed){
					$unreadCount += $this->countAllStatus($feed->getId(), StatusFlag::UNREAD);
				}
				break;

			case FeedType::FEED:
				$unreadCount = $this->countAllStatus($feedId, StatusFlag::UNREAD);
				break;
		}

		return (int)$unreadCount;
	}


	/**
	 * Returns all items
	 * @param $feedType the type of the feed
	 * @param $feedId the id of the feed or folder
	 * @param $showAll if true, it will also include unread items
	 * @return an array with all items
	 */
	public function getItems($feedType, $feedId, $showAll){
		$items = array();

		// starred or subscriptions
		if ($feedType === FeedType::STARRED || $feedType === FeedType::SUBSCRIPTIONS) {

			if($feedType === FeedType::STARRED){
				$statusFlag = StatusFlag::IMPORTANT;
			}

			if($feedType === FeedType::SUBSCRIPTIONS){
				$statusFlag = StatusFlag::UNREAD;
			}

			$items = $this->findEveryItemByStatus($statusFlag);

		// feed
		} elseif ($feedType === FeedType::FEED){

			if($showAll) {
				$items = $this->findByFeedId($feedId);
			} else {
				$items = $this->findAllStatus($feedId, StatusFlag::UNREAD);
			}

		// folder
		} elseif ($feedType === FeedType::FOLDER){
			$feedMapper = new FeedMapper($this->userId);
			$feeds = $feedMapper->findByFolderId($feedId);

			foreach($feeds as $feed){
				if($showAll) {
					$items = array_merge($items, $this->findByFeedId($feed->getId()));
				} else {
					$items = array_merge($items,
						$this->findAllStatus($feed->getId(), StatusFlag::UNREAD));
				}
			}
		}
		return $items;
	}
}