.. include:: /Includes.rst.txt

.. _cli:

============
CLI Commands
============

This extension ships with one console command.

.. _cli-download:

Download images from Oracle DAM
===============================

:bash:`vendor/bin/typo3 oracledam:download ASSET_ID [ASSET_ID ...]`

Arguments
---------

.. confval:: ASSET_ID

   :Required: true
   :type: string
   :Example: CONT0123456789ABCDEF0123456789ABCDEF

   The ID of an asset to download. You can supply one or many asset IDs.
