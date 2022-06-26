<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Controller;

use Oracle\Typo3Dam\Configuration\ExtensionConfigurationManager;
use Oracle\Typo3Dam\Controller\Exception\MissingParameterException;
use Oracle\Typo3Dam\Domain\Repository\SysFileRepository;
use Oracle\Typo3Dam\Service\AssetService;
use Oracle\Typo3Dam\Service\Exception\AssetDoesNotExistException;
use Oracle\Typo3Dam\Service\Exception\FileIsNotAnAssetException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class AssetListModuleController
{
    protected const ALLOWED_ACTIONS = [
        'list',
        'updateFile',
    ];

    /**
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * @var ExtensionConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @param ModuleTemplate|null $moduleTemplate
     * @param ExtensionConfigurationManager|null $configurationManager
     * @param UriBuilder|null $uriBuilder
     */
    public function __construct(
        ModuleTemplate $moduleTemplate = null,
        ExtensionConfigurationManager $configurationManager = null,
        UriBuilder $uriBuilder = null
    ) {
        $this->moduleTemplate = $moduleTemplate ?? GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->configurationManager = $configurationManager
            ?? GeneralUtility::makeInstance(ExtensionConfigurationManager::class);
        $this->uriBuilder = $uriBuilder ?? GeneralUtility::makeInstance(UriBuilder::class);
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

        $this->getLanguageService()->includeLLFile('EXT:oracle_dam/Resources/Private/Language/locallang.xlf');

        $result = $this->{$action . 'Action'}($request);

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        $this->moduleTemplate->setContent($this->view->render());

        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function listAction(ServerRequestInterface $request): void
    {
        $this->setDocHeader('list');

        GeneralUtility::makeInstance(PageRenderer::class)
            ->loadRequireJsModule('TYPO3/CMS/Filelist/FileList');

        $fileRepository = GeneralUtility::makeInstance(SysFileRepository::class);

        $this->view->assignMultiple(
            [
                'dateFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
                'timeFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
                'oceDomain' => $this->configurationManager->getOceDomain(),
                'files' => $fileRepository->findFromOracle(),
            ]
        );
    }

    /**
     * Update a file and its metadata.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function updateFileAction(ServerRequestInterface $request): ResponseInterface
    {
        $fileUid = (int)$request->getQueryParams()['file'] ?? null;

        if ($fileUid === null) {
            throw new MissingParameterException(
                'Missing file parameter.',
                $request
            );
        }

        $redirectResponse = GeneralUtility::makeInstance(
            RedirectResponse::class,
            $this->uriBuilder->buildUriFromRoute('file_oracleDamAssetList')
        );

        try {
            $file = GeneralUtility::makeInstance(ResourceFactory::class)->getFileObject($fileUid);
        } catch (FileDoesNotExistException $exception) {
            $this->addFlashMessage(
                sprintf(
                    $this->getLanguageService()->getLL('module.action.update.fileNotFoundMessage'),
                    (string)$fileUid
                ),
                $this->getLanguageService()->getLL('module.action.update.fileNotFoundTitle'),
                FlashMessage::ERROR
            );

            return $redirectResponse;
        }

        $assetService = GeneralUtility::makeInstance(AssetService::class);

        try {
            $assetService->synchronizeMetadata($file);
            $assetService->updateLocalAsset($file);
        } catch (AssetDoesNotExistException $exception) {
            $this->addFlashMessage(
                sprintf(
                    $this->getLanguageService()->getLL('module.action.update.assetNotFoundMessage'),
                    $file->getName()
                ),
                $this->getLanguageService()->getLL('module.action.update.assetNotFoundTitle'),
                FlashMessage::ERROR
            );

            return $redirectResponse;
        } catch (FileIsNotAnAssetException $exception) {
            $this->addFlashMessage(
                sprintf(
                    $this->getLanguageService()->getLL('module.action.update.notADamFileMessage'),
                    $file->getName()
                ),
                $this->getLanguageService()->getLL('module.action.update.notADamFileTitle'),
                FlashMessage::ERROR
            );

            return $redirectResponse;
        }

        $this->addFlashMessage(
            sprintf(
                $this->getLanguageService()->getLL('module.action.update.successMessage'),
                $file->getName()
            ),
            $this->getLanguageService()->getLL('module.action.update.successTitle')
        );

        return $redirectResponse;
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

    /**
     * Creates and queues a flash message.
     *
     * @param string $message
     * @param string $header
     * @param int $severity
     */
    protected function addFlashMessage(string $message, string $header, int $severity = FlashMessage::OK)
    {
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->addMessage(GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $header,
            $severity,
            true
        ));
    }
}
