<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Controller;

use Oracle\Typo3Dam\Service\AssetService;
use Oracle\Typo3Dam\Service\Exception\AssetDoesNotExistException;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SelectorController
{
    /**
     * @var AssetService
     */
    protected $assetService;

    /**
     * @param AssetService $assetService
     */
    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;

        $this->getLanguageService()->includeLLFile('EXT:oracle_dam/Resources/Private/Language/locallang.xlf');
    }

    /**
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function downloadFileAction(ServerRequestInterface $request): JsonResponse
    {
        $assetIds = GeneralUtility::trimExplode(',', $request->getParsedBody()['assets'], true);

        $fileUids = [];
        $errors = [];

        foreach ($assetIds as $assetId) {
            try {
                $file = $this->assetService->createLocalAssetCopy($assetId);
            } catch (AssetDoesNotExistException $exception) {
                $errors[] = str_replace(
                    '{0}',
                    $assetId,
                    $this->getLanguageService()->getLL('js.modal.error.assetDoesNotExist')
                        ?? 'js.modal.error.assetDoesNotExist'
                );

                continue;
            } catch (InsufficientFolderAccessPermissionsException $exception) {
                $errors[] = $this->getLanguageService()->getLL('js.modal.error.insufficientFolderAccessPermissions')
                    ?? 'js.modal.error.insufficientFolderAccessPermissions';

                continue;
            }

            if ($file !== null) {
                $fileUids[] = $file->getUid();
            }
        }

        return $this->getSuccessResponse([
            'fileUids' => $fileUids,
            'errors' => $errors,
        ]);
    }

    /**
     * Returns an error response object with message set.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function getErrorResponse(string $message): JsonResponse
    {
        return GeneralUtility::makeInstance(JsonResponse::class)
            ->withStatus(
                500,
                $message
            )
            ->setPayload([
                'success' => false,
                'message' => $message,
            ]);
    }

    /**
     * Returns a success response object with message set.
     *
     * @param array $data
     * @return JsonResponse
     */
    protected function getSuccessResponse(array $data): JsonResponse
    {
        $data['success'] = true;

        return GeneralUtility::makeInstance(JsonResponse::class)
            ->withStatus(200)
            ->setPayload($data);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
