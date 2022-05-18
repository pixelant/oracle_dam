define([
  'nprogress',
  'TYPO3/CMS/Backend/Modal',
  'TYPO3/CMS/Backend/Severity',
  'TYPO3/CMS/Backend/Utility/MessageUtility',
  'TYPO3/CMS/Core/Ajax/AjaxRequest',
  'TYPO3/CMS/Core/DocumentService'
], function(
  NProgress,
  Modal,
  Severity,
  MessageUtility,
  AjaxRequest,
  DocumentService
) {
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
        callback: function(modal) {
          require(
            [TYPO3.settings.oracle_dam.jsUiUrl],
            function () {
              OracleCEUI.oceUrl = 'https://' + TYPO3.settings.oracle_dam.oceDomain;

              var frame = OracleCEUI.assetsView.createFrame({
                'assetsView': {
                  'select': 'single',
                  'filter': {
                    'bar': {
                      'capsules': false,
                      'relatedKeywords': true
                    },
                    'repositories': [
                      'none'
                    ],
                    'channels': [
                      TYPO3.settings.oracle_dam.channelId
                    ],
                    'mediaGroups': [
                      'images'
                    ],
                    'assetStatus': [
                      'published'
                    ]
                  },
                  'filterValue': {
                    'repositoryId': TYPO3.settings.oracle_dam.repositoryId,
                    'channelId': TYPO3.settings.oracle_dam.channelId,
                    'mediaGroups': [
                      'images'
                    ],
                    'assetStatus': [
                      'published'
                    ]
                  }
                }
              });

              $('.modal-body', modal).append(frame);
            }
          );
        }
      });
    }
  }

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
    require([TYPO3.settings.oracle_dam.jsUiUrl]);
  });
});
