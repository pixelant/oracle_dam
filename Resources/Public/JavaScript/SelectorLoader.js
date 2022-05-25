define([
  'nprogress',
  'TYPO3/CMS/Backend/Modal',
  'TYPO3/CMS/Backend/Severity',
  'TYPO3/CMS/Backend/Utility/MessageUtility',
  'TYPO3/CMS/Core/Ajax/AjaxRequest',
  'TYPO3/CMS/Core/DocumentService',
], function (NProgress, Modal, Severity, MessageUtility, AjaxRequest, DocumentService) {
  'use strict';

  const SelectorPlugin = function (element) {
    const self = this;

    self.$button = $(element);
    self.irreObjectId = self.$button.data('fileIrreObject');
    self.allowedExtensions = self.$button.data('fileAllowed').split(',');

    self.selectedAssets = [];

    self.openModal = function () {
      self.$modal = Modal.advanced({
        type: Modal.types.default,
        title: 'Oracle DAM',
        content: '',
        severity: Severity.default,
        size: Modal.sizes.full,
        buttons: [
          {
            text: 'Cancel',
            active: false,
            trigger: function () {
              self.selectedAssets = [];
              self.$modal.modal('hide');
            },
            btnClass: 'btn-default'
          },
          {
            text: 'Use Selected',
            active: false,
            trigger: function () {
              self.$modal.modal('hide');

              self.addAssets(self.selectedAssets);
            },
            btnClass: 'btn-success'
          }
        ],
        callback: function (modal) {
          window.top.require([TYPO3.settings.oracle_dam.jsUiUrl], function () {
            window.top.OracleCEUI.oceUrl = 'https://' + TYPO3.settings.oracle_dam.oceDomain;

            const frame = window.top.OracleCEUI.assetsView.createFrame(
              {
                assetsView: {
                  select: 'single',
                  filter: {
                    bar: {
                      capsules: false,
                      relatedKeywords: true,
                    },
                    repositories: ['none'],
                    channels: [TYPO3.settings.oracle_dam.channelId],
                    mediaGroups: ['images'],
                    assetStatus: ['published'],
                  },
                  filterValue: {
                    repositoryId: TYPO3.settings.oracle_dam.repositoryId,
                    channelId: TYPO3.settings.oracle_dam.channelId,
                    mediaGroups: ['images'],
                    assetStatus: ['published'],
                  },
                },
              },
              {
                selectionChanged: async function (frame, selection) {
                  self.selectedAssets = selection;

                  var $successButton = $('.modal-footer .btn-success', self.$modal).first();
                  console.log($successButton, selection);
                  if (selection.length === 0) {
                    $successButton.attr('disabled', true);

                    return;
                  }

                  $successButton.attr('disabled', false);
                }
              }
            );

            const $modalBody = $('.modal-body', modal);

            $(frame)
              .css('height', '100%')
              .css('width', '100%');

            $modalBody
              .empty()
              .css('padding', '0')
              .get(0)
              .appendChild(frame);
          });
        },
      });
    };

    self.addAssets = function (assets) {
      NProgress.start();

      const request = new AjaxRequest(TYPO3.settings.ajaxUrls['oracle_dam_download_file']);

      const assetIds = [];

      for (let asset of assets) {
        assetIds.push(asset.id);
      }

      request.post({
        assets: assetIds.join(',')
      }).then(
        async function (response) {
          const data = await response.resolve();

          if (response.response.status !== 200 || !data.success) {
            let errorMessage = TYPO3.lang['oracle_dam.modal.request-failed'];

            if (data.message) {
              errorMessage = data.message;
            }

            self.displayError(errorMessage);

            NProgress.done();

            return;
          }

          for (let fileUid in data.fileUids) {
            MessageUtility.send({
              actionName: 'typo3:foreignRelation:insert',
              objectGroup: self.irreObjectId,
              table: 'sys_file',
              uid: fileUid
            });
          }

          NProgress.done();
        },
        function (error) {
          self.displayError(TYPO3.lang['oracle_dam.modal.request-failed'] + error.status + ' ' + error.statusText);

          NProgress.done();
        }
      );
    };

    /**
     * Displays an error message in a modal.
     *
     * @param message The error message to display
     */
    self.displayError = function (message) {
      const errorModal = Modal.confirm(
        TYPO3.lang['oracle_dam.modal.error-title'],
        message,
        Severity.error,
        [{
          text: TYPO3.lang['button.ok'] || 'OK',
          btnClass: 'btn-' + Severity.getCssClass(Severity.error),
          name: 'ok',
          active: true,
        }]
      ).on('confirm.button.ok', function () {
        errorModal.modal('hide');
      });
    };
  };

  DocumentService.ready().then(function () {
    document.addEventListener(
      'click',
      function (event) {
        if (!event.target.matches('.t3js-oracleDam-selector-btn')) {
          return;
        }

        event.preventDefault();

        new SelectorPlugin(event.target).openModal();
      },
      false
    );

    // Preload dependency.
    window.top.require([TYPO3.settings.oracle_dam.jsUiUrl]);
  });
});
