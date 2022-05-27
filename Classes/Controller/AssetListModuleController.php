<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class AssetListModuleController
{
    protected const ALLOWED_ACTIONS = [
        'list',
    ];

    /**
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * @var StandaloneView
     */
    protected $view;

    public function __construct(ModuleTemplate $moduleTemplate = null)
    {
        $this->moduleTemplate = $moduleTemplate ?? GeneralUtility::makeInstance(ModuleTemplate::class);
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $action = (string)($request->getQueryParams()['action'] ?? $request->getParsedBody()['action'] ?? 'list');

        if (!in_array($action, self::ALLOWED_ACTIONS)) {
            return new HtmlResponse('Action not allowed', 400);
        }

        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplateRootPaths(['EXT:oracle_dam/Resources/Private/Templates/AssetListModule']);
        $this->view->setPartialRootPaths(['EXT:oracle_dam/Resources/Private/Partials/']);
        $this->view->setLayoutRootPaths(['EXT:oracle_dam/Resources/Private/Layouts/']);
        $this->view->setTemplate($action);

        $result = $this->{$action . 'Action'}($request);

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        $this->getLanguageService()->includeLLFile('EXT:oracle_dam/Resources/Private/Language/Modules/asset_list.xlf');

        $this->moduleTemplate->setContent($this->view->render());

        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    public function listAction(): void
    {
        $this->setDocHeader('list');

        $this->view->assignMultiple(
            [
                'some-variable' => 'some-value',
            ]
        );
    }

    private function setDocHeader(string $active)
    {
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
