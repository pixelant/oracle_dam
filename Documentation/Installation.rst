.. include:: ../Includes.txt

.. _installation:

============
Installation
============

1. Install the extension using Composer: `composer req oracle/typo3-dam`

2. **In TYPO3 v10:** Activate the extension in TYPO3 by using the
   *Admin Tools > Extensions* module
   or by running :bash:`vendor/bin/typo3 extension:activate qbank; vendor/bin/typo3cms database:updateschema` in the command line.

   **In TYPO3 v11:** Update the database schema by running
   :bash:`vendor/bin/typo3cms database:updateschema` in the command line.

3. Configure the DAM integration by supplying the required configuration values.
   This is explained in the :ref:`configuration` section.
