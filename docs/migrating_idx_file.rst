Migrating your idx file
=======================

Since version 0.2.0 we started supporting a new idxfile format. We think the new format is more easy to write
and we will drop support for the old format soon. If you're still using the old idxfile format you can avoid migrating
right now, just be sure to create an instance of `\Idephix\Config` to construct your Idephix instance instead of the
array you're using right now.

Implementing this should be easy enough as the `\Idephix\Config` object can be created from an array, see
:ref:`idx_config` for more information.

If you're brave enough and want to jump on the innovation wagon right now read :ref:`writing_tasks` on how to update
your idxfile.