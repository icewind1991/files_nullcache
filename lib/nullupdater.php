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
use OC\Files\Cache\Updater;

/**
 * Update the cache and propagate changes
 *
 */
class NullUpdater extends Updater {
	public function propagate($path, $time = null) {
		return;
	}

	public function update($path, $time = null) {
		return;
	}

	public function remove($path) {
		return;
	}

	public function renameFromStorage(\OC\Files\Storage\Storage $sourceStorage, $source, $target) {
		$sourceStorage->getCache()->remove($source);
	}

	public function correctParentStorageMtime($internalPath) {
		return;
	}
}
