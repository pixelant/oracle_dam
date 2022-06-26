<?php

declare(strict_types=1);


namespace Oracle\Typo3Dam\Service\Exception;

/**
 * Thrown if a local file is not an Oracle DAM asset, though it was expected to be one.
 */
class FileIsNotAnAssetException extends AbstractAssetServiceException
{
}
