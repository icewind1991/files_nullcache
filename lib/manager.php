<?php
/**
 * Copyright (c) 2015 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_NullCache;

use OC\Files\Filesystem;
use OC\Files\Storage\Local;
use OC\Files\Storage\Storage;

class Manager {
	public function setupStorageWrapper() {
		Filesystem::addStorageWrapper('crawl', function ($mountPoint, Storage $storage) {
			if ($storage instanceof Local) {
				return new NullCacheWrapper(['storage' => $storage]);
			} else {
				return $storage;
			}
		}, 99999);
	}
}
