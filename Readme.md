# Files_NullCache

A dummy storage backend that removes the filecache for testing purposes

Any attempt to write to the cache will throw an exception to help tracking down all parts of ownCloud that manipulate the cache directly.
