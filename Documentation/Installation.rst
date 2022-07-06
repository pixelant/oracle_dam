.. include:: /Includes.rst.txt

.. _installation:

======================
Installation and Setup
======================

1. Install the extension using Composer: :bash:`composer req oracle/typo3-dam`

2. **In TYPO3 v10:** Activate the extension in TYPO3 by using the
   *Admin Tools > Extensions* module
   or by running :bash:`vendor/bin/typo3 extension:activate qbank; vendor/bin/typo3cms database:updateschema` in the command line.

   **In TYPO3 v11:** Update the database schema by running
   :bash:`vendor/bin/typo3cms database:updateschema` in the command line.

3. **In Oracle Identity Cloud Service:** The *Identity Domain Administrator* or
   *Application Administrator* of the Oracle Content tenant must create an
   *OAuth client*. This process results in the credentials used when
   authenticating the TYPO3 extension. Read more in the `Oracle Content
   Documentation <https://docs.oracle.com/en/cloud/paas/content-cloud/solutions/integrate-oracle-content-management-using-oauth.html#GUID-E082EB23-9EB1-4E6D-9996-F3CD4862D072>`.

4. **In Oracle Identity Cloud Service:** Add the client app to the
   *CECEnterpriseUser*, *CECRepositoryAdmin*, and *CECContentAdministrator*
   application roles.

   .. image:: Images/ApplicationsAssignments.png

      An app has been added to the *CECContentAdministrator* role

5. **In Oracle Content:** Add the client app to the repository with a *Manager*
   role.

   .. image:: Images/AddRepositoryMember.png

      The app added as a repository manager.

6. **In TYPO3:** Configure the DAM integration by supplying the required
   configuration values. This is explained in the :ref:`configuration` section.
