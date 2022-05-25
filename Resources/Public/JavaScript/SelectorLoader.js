define([
  'nprogress',
  'TYPO3/CMS/Backend/Modal',
  'TYPO3/CMS/Backend/Severity',
  'TYPO3/CMS/Backend/Utility/MessageUtility',
  'TYPO3/CMS/Core/Ajax/AjaxRequest',
  'TYPO3/CMS/Core/DocumentService',
], function (NProgress, Modal, Severity, MessageUtility, AjaxRequest, DocumentService) {
  'use strict';

  var SelectorPlugin = function (element) {
    var self = this;

    self.openModal = function () {
      self.$modal = Modal.advanced({
        type: Modal.types.default,
        title: 'Oracle DAM',
        content: '',
        severity: Severity.default,
        size: Modal.sizes.full,
        callback: function (modal) {
          window.top.require([TYPO3.settings.oracle_dam.jsUiUrl], function () {
            window.top.OracleCEUI.oceUrl = 'https://' + TYPO3.settings.oracle_dam.oceDomain;

            var frame = window.top.OracleCEUI.assetsView.createFrame({
              assetsView: {
                select: 'single',
                filter: {
                  bar: {
                    capsules: false,
                    relatedKeywords: true,
                  },
                  repositories: ['none'],
                  channels: [],
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
            });

            var $modalBody = $('.modal-body', modal);

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
