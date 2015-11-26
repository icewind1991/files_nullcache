<?php
/**
 * @author Robin Appelman <icewind@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files_NullCache;

use OC\Files\Storage\Wrapper\Wrapper;

/**
 * Specialized version of Local storage with no filecache
 */
class NullCacheWrapper extends Wrapper {
	/**
	 * @var \OC\Files\Storage\Local $storage
	 */
	protected $storage;

	private $propagator;
	private $updater;
	private $scanner;
	private $cache;

	/**
	 * @param string $path
	 * @param \OC\Files\Storage\Local|null $storage
	 * @return \OC\Files\Cache\HomeCache
	 */
	public function getCache($path = '', $storage = null) {
		if (!isset($this->cache)) {
			$this->cache = new NullCache($this->storage);
		}
		return $this->cache;
	}

	public function getScanner($path = '', $storage = null) {
		if (!isset($this->scanner)) {
			$this->scanner = new NullScanner($this->storage);
		}
		return $this->scanner;
	}

	/**
	 * get a propagator instance for the cache
	 *
	 * @param \OC\Files\Storage\Storage (optional) the storage to pass to the watcher
	 * @return \OC\Files\Cache\Propagator
	 */
	public function getPropagator($storage = null) {
		if (!isset($this->propagator)) {
			$this->propagator = new NullPropagator($this->storage);
		}
		return $this->propagator;
	}

	public function getUpdater($storage = null) {
		if (!isset($this->updater)) {
			$this->updater = new NullUpdater($this->storage);
		}
		return $this->updater;
	}

	public function hasUpdated($path, $time) {
		return false;
	}
}
