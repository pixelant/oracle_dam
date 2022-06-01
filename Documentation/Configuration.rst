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
   :Example: myinstance.cec.ocp.oraclecloud.com

   The domain of the remote Oracle Content DAM instance.

.. confval:: repositoryID

   :Required: true
   :type: string
   :Example: 0123456789ABCDEF0123456789ABCDEF

   The domain of the remote Oracle Content DAM instance.

