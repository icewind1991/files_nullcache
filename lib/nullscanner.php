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

use OC\Files\Cache\Scanner;

class NullScanner extends Scanner {
	public function backgroundScan() {
		return;
	}

	public function scan($path, $recursive = self::SCAN_RECURSIVE, $reuse = -1, $lock = true) {
		throw new \Exception('Not supported');
	}

	public function scanFile($file, $reuseExisting = 0, $parentId = -1, $cacheData = null, $lock = true) {
		throw new \Exception('Not supported');
	}
}
