<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\FormEngine\Container;

use Oracle\Typo3Dam\Configuration\ExtensionConfigurationManager;
use TYPO3\CMS\Backend\Form\Container\InlineControlContainer;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class SelectorButtonContainer extends InlineControlContainer
{
    protected $extensionConfigurationManager;

    /**
     * @param NodeFactory $nodeFactory
     * @param array $data
     */
    public function __construct(
        NodeFactory $nodeFactory,
        array $data
    ) {
        parent::__construct($nodeFactory, $data);

        $this->extensionConfigurationManager = GeneralUtility::makeInstance(ExtensionConfigurationManager::class);
    }

    /**
     * @param array $inlineConfiguration
     * @return string
     */
    protected function renderPossibleRecordsSelectorTypeGroupDB(array $inlineConfiguration): string
    {
        $selector = parent::renderPossibleRecordsSelectorTypeGroupDB($inlineConfiguration);

        $button = $this->renderButton($inlineConfiguration);

        // Inject button before help-block
        if (strpos($selector, '</div><div class="help-block">') > 0) {
            $selector = str_replace(
                '</div><div class="help-block">',
                $button . '</div><div class="help-block">',
                $selector
            );
        // Try to inject it into the form-control container
        } elseif (preg_match('/<\/div><\/div>$/i', $selector)) {
            $selector = preg_replace('/<\/div><\/div>$/i', $button . '</div></div>', $selector);
        } else {
            $selector .= $button;
        }

        return $selector;
    }

    protected function renderButton(array $inlineConfiguration)
    {
        if (
            $this->extensionConfigurationManager->getDownloadFolder() === null
            || !$this->extensionConfigurationManager->isConfigured()
        ) {
            return '';
        }

        $this->addJavaScriptConfiguration();
        $this->addJavaScriptLocalization();

        $appearanceConfiguration = $inlineConfiguration['selectorOrUniqueConfiguration']['config']['appearance'];

        $allowed = $appearanceConfiguration['oracleDam_BrowserAllowed']
            ?? $appearanceConfiguration['elementBrowserAllowed'];

        $allowedArray = GeneralUtility::trimExplode(',', $allowed, true);
        if (empty($allowedArray)) {
            $allowedArray = GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], true);
        }
        $allowed = implode(',', $allowedArray);

        $currentStructureDomObjectIdPrefix = $this->inlineStackProcessor->getCurrentStructureDomObjectIdPrefix(
            $this->data['inlineFirstPid']
        );
        $objectPrefix = $currentStructureDomObjectIdPrefix . '-' . $inlineConfiguration['foreign_table'];

        $this->requireJsModules[] = 'TYPO3/CMS/Oracle/Typo3Dam';

        $buttonLabel = htmlspecialchars(LocalizationUtility::translate('selector-button-control.label', 'oracle_dam'));
        $titleText = htmlspecialchars(LocalizationUtility::translate('selector-button-control.title', 'oracle_dam'));

        $button = '
            <button type="button" class="btn btn-default t3js-oracleDam-selector-btn"
                data-title="' . htmlspecialchars($titleText) . '"
                data-file-irre-object="' . htmlspecialchars($objectPrefix) . '"
                data-file-allowed="' . htmlspecialchars($allowed) . '"
                ' . $this->inlineStyleAttribute() . '
                >
                ' . $this->iconFactory->getIcon('tx-oracle-logo', Icon::SIZE_SMALL)->render() . '
                ' . $buttonLabel .
            '</button>';

        return $button;
    }

    /**
     * Adds localization string for JavaScript use.
     */
    protected function addJavaScriptLocalization(): void
    {
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        $pageRenderer->addInlineLanguageLabelArray([
            'oracle_dam.modal.error-title' => $this->translate('js.modal.error-title'),
            'oracle_dam.modal.request-failed' => $this->translate('js.modal.request-failed'),
            'oracle_dam.modal.request-failed-error' => $this->translate('js.modal.request-failed-error'),
            'oracle_dam.modal.illegal-extension' => $this->translate('js.modal.illegal-extension'),
        ]);
    }

    /**
     * Populates a configuration array that will be available in JavaScript as
     * TYPO3.settings.FormEngineInline.oracle_dam.
     */
    protected function addJavaScriptConfiguration(): void
    {
        $configuration = [
            'oceUrl' => $this->extensionConfigurationManager->getOceDomain(),
            'channelId' => $this->extensionConfigurationManager->getChannelID(),
            'repositoryId' => $this->extensionConfigurationManager->getRepositoryID(),
        ];

        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        $pageRenderer->addInlineSettingArray('oracle_dam', $configuration);
    }

    /**
     * Returns a translated string for $key.
     *
     * @param string $key
     * @return string|null
     */
    protected function translate(string $key): ?string
    {
        return LocalizationUtility::translate($key, 'oracle_dam');
    }

    /**
     * Generate inline style attribute if needed.
     * Partly based on render function of
     * TYPO3\CMS\Backend\Form\Container\InlineControlContainer.
     *
     * @return string
     */
    protected function inlineStyleAttribute(): string
    {
        $inlineStyles = [];
        $parameterArray = $this->data['parameterArray'];

        $config = $parameterArray['fieldConf']['config'];
        $isReadOnly = isset($config['readOnly']) && $config['readOnly'];

        $numberOfFullyLocalizedChildren = 0;
        foreach ($parameterArray['fieldConf']['children'] as $child) {
            if (!$child['isInlineDefaultLanguageRecordInLocalizedParentContext']) {
                $numberOfFullyLocalizedChildren++;
            }
        }

        if ($isReadOnly || $numberOfFullyLocalizedChildren >= $config['maxitems']) {
            $inlineStyles[] = 'display: none';
        }

        if (count($inlineStyles) > 0) {
            return 'style="' . implode(';', $inlineStyles) . '"';
        }

        return '';
    }
}
