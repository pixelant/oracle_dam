<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Controller;

use Oracle\Typo3Dam\Service\AssetService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
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
    }

    public function downloadFileAction(ServerRequestInterface $request): JsonResponse
    {
        return $this->getSuccessResponse([]);
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
     * @param string $message
     * @return JsonResponse
     */
    protected function getSuccessResponse(array $data): JsonResponse
    {
        $data['success'] = true;

        return GeneralUtility::makeInstance(JsonResponse::class)
            ->withStatus(200)
            ->setPayload($data);
    }
}
