.. include:: /Includes.rst.txt

.. _configuration:

=============
Configuration
=============

.. _extension_configuration:

Extension Configuration
=======================

In order for the extension to work, it must be configured.
In the TYPO3 Backend, navigate to
*Admin Tools > Settings > Extension Configuration > oracle_dam*
and set the required global configuration options in the "Basic" tab.
Configuration options in the "Advanced" tab are optional.

Configuration options can also be set using environment variables.
These will override any setting made in the Extension Configuration module.

.. _configuration-basic:

Basic
-----

.. confval:: oceDomain

   :Required: true
   :type: string
   :Environment variable: APP_ORACLE_DAM_DOMAIN
   :Example: myinstance.cec.ocp.oraclecloud.com

   The domain of the remote Oracle Content DAM instance.

.. confval:: repositoryId

   :Required: true
   :type: string
   :Environment variable: APP_ORACLE_DAM_REPOSITORY
   :Example: 0123456789ABCDEF0123456789ABCDEF

   The ID of the repository to use when selecting images.

.. confval:: channelId

   :Required: true
   :type: string
   :Environment variable: APP_ORACLE_DAM_CHANNEL
   :Example: RCHANNEL0123456789ABCDEF0123456789ABCDEF

   The channel ID to use when selecting images.

.. _configuration-advanced:

Advanced
--------

.. confval:: jsUiUrl

   :Required: false
   :type: url
   :Environment variable: APP_ORACLE_DAM_JS_URL
   :Default: https://static.ocecdn.oraclecloud.com/cdn/cec/api/oracle-ce-ui-2.11.js

   The URL to the JavaScript file for the image selector UI.
