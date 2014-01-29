Testing search lucene
=====================

hooks.php
---------

make sure
- [ ] indexFile() registers an IndexJob
- [ ] renameFile() deletes the file from lucene index and adds an IndexJob
- [ ] deleteFile() deletes the file from lucene index 

indexer.php
-----------

- [ ] indexFile() adds file to the index if possible (different filetypes) 
- [ ] indexFiles() indexes files and changes status accordingly


indexjob.php
------------

- [ ] run() sets up correct FS and indexes all unindexed files

lucene.php
----------

- [ ] openOrCreate() creates the index on the fly, opens existing index, check readonly index?
- [ ] optimizeIndex() optimizes the index?
- [ ] updateFile() deletes the old entry and adds a new one
- [ ] deleteFile() deletes the old entry
- [ ] find() finds an entry, try various queries

optimizejob.php
---------------

- [ ] run() cleans up entries without a fileid (removing old pk entries), optimizes the index

searchprovider.php
------------------

- [x] creates valid oc search result objects

status.php
----------


- [x] fromFileId() loads a status
- [x] markNew() sets status to N
- [x] markIndexed() sets status to I
- [x] markSkipped() sets status to S
- [x] markError() sets status to E
- [ ] delete() deletes a status
- [ ] getUnindexed() return list of unindexed file ids
- [ ] getDeleted() returns list of deleted file ids

testcase.php
------------

- [x] setUp() create folder and two files

