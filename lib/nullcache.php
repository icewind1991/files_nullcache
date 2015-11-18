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

use OC\Files\Cache\Cache;
use OC\Files\Storage\Local;

class NullCache extends Cache {
	const FOLDER_MIME = 'httpd/unix-directory';

	/**
	 * @var array [$id => $path]
	 */
	private $idMap = [];

	/**
	 * @var \OC\Files\Storage\Local
	 */
	private $storage;

	/**
	 * @param \OC\Files\Storage\Local $storage
	 */
	public function __construct(Local $storage) {
		parent::__construct($storage);
		$this->storage = $storage;
	}

	/**
	 * @param string $path
	 * @return array
	 *
	 * the returned cache entry contains at least the following values:
	 * [
	 *        'fileid' => int, the numeric id of a file (see getId)
	 *        'storage' => int, the numeric id of the storage the file is stored on
	 *        'path' => string, the path of the file within the storage ('foo/bar.txt')
	 *        'name' => string, the basename of a file ('bar.txt)
	 *        'mimetype' => string, the full mimetype of the file ('text/plain')
	 *        'mimepart' => string, the first half of the mimetype ('text')
	 *        'size' => int, the size of the file or folder in bytes
	 *        'mtime' => int, the last modified date of the file as unix timestamp as shown in the ui
	 *        'storage_mtime' => int, the last modified date of the file as unix timestamp as stored on the storage
	 *            Note that when a file is updated we also update the mtime of all parent folders to make it visible to the user which folder has had updates most recently
	 *            This can differ from the mtime on the underlying storage which usually only changes when a direct child is added, removed or renamed
	 *        'etag' => string, the etag for the file
	 *            An etag is used for change detection of files and folders, an etag of a file changes whenever the content of the file changes
	 *            Etag for folders change whenever a file in the folder has changed
	 *        'permissions' int, the permissions for the file stored as bitwise combination of \OCP\PERMISSION_READ, \OCP\PERMISSION_CREATE
	 *            \OCP\PERMISSION_UPDATE, \OCP\PERMISSION_DELETE and \OCP\PERMISSION_SHARE
	 * ]
	 */
	public function get($path) {
		static $preventRecursion;
		if ($preventRecursion) {
			return [];
		}
		$preventRecursion = true;
		$data = $this->storage->getMetaData($path);
		$data['path'] = $path;
		$data['name'] = basename($path);
		$stat = $this->storage->stat($path);
		$preventRecursion = false;
		$data['fileid'] = $stat['ino'];
		$this->idMap[$data['fileid']] = $path;
		$mimeParts = explode('/', $data['mimetype'], 2);
		$data['mimepart'] = $mimeParts[0];
		$data['storage_mtime'] = $data['mtime'];
		$data['parent'] = $this->getParentId($path);
		if ($data['mimetype'] === self::FOLDER_MIME) {
			$data['size'] = 1;//WIP
		}
		return $data;
	}

	/**
	 * get the metadata of all files stored in $folder
	 *
	 * @param string $folder
	 * @return array
	 */
	public function getFolderContents($folder) {
		$dh = $this->storage->opendir($folder);
		$files = [];
		while (($file = readdir($dh)) !== false) {
			$files[] = $folder . '/' . $file;
		}
		return array_map(function ($file) {
			return $this->get($file);
		}, $files);
	}

	/**
	 * get the metadata of all files stored in $folder
	 *
	 * @param int $fileId the file id of the folder
	 * @return array
	 */
	public function getFolderContentsById($fileId) {
		$path = $this->getPathById($fileId);
		return $this->getFolderContents($path);
	}

	public function put($file, array $data) {
		throw new \Exception('Not supported');
	}

	public function update($id, array $data) {
		throw new \Exception('Not supported');
	}

	public function getId($file) {
		$stat = $this->storage->stat($file);
		return $stat['ino'];
	}

	public function remove($file) {
		throw new \Exception('Not supported');
	}

	public function move($source, $target) {
		throw new \Exception('Not supported');
	}

	public function moveFromCache(Cache $sourceCache, $sourcePath, $targetPath) {
		throw new \Exception('Not supported');
	}

	public function clear() {
		throw new \Exception('Not supported');
	}

	public function getStatus($file) {
		return $this->storage->file_exists($file) ? parent::COMPLETE : parent::NOT_FOUND;
	}

	public function search($pattern) {
		$all = $this->getAll();
		return array_filter($all, function (array $entry) use ($pattern) {
			return strpos($entry['name'], $pattern) !== false;
		});
	}

	public function searchByMime($mimetype) {
		$all = $this->getAll();
		return array_filter($all, function (array $entry) use ($mimetype) {
			return $entry['mimetype'] === $mimetype || $entry['mimepart'] === $mimetype;
		});
	}

	public function searchByTag($tag, $userId) {
		return [];
	}

	public function correctFolderSize($path, $data = null) {
		throw new \Exception('Not supported');
	}

	public function calculateFolderSize($path, $entry = null) {
		throw new \Exception('Not supported');
	}

	public function getAll() {
		$inFolders = [''];
		$result = [];

		while (count($inFolders) > 0) {
			$folder = array_pop($inFolders);

			$content = $this->getFolderContents($folder);
			$result = array_merge($result, $content);

			$subFolders = array_filter($content, function (array $data) {
				return $data['mimetype'] === self::FOLDER_MIME;
			});
			$subFolderPaths = array_map(function (array $data) {
				return $data['path'];
			}, $subFolders);

			$inFolders = array_map($inFolders, $subFolderPaths);
		}

		return $result;
	}

	public function getIncomplete() {
		return [];
	}

	public function getPathById($id) {
		if (isset($this->idMap[$id])) {
			return $this->idMap[$id];
		} else {
			$all = $this->getAll();
			$matches = array_values(array_filter($all, function (array $entry) use ($id) {
				return $entry['fileid'] === $id;
			}));
			if (count($matches) > 0) {
				$this->idMap[$id] = $matches[0]['path'];
				return $this->idMap[$id];
			} else {
				return null;
			}
		}
	}
}
