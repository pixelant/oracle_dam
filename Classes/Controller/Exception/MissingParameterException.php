<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Controller\Exception;

use GuzzleHttp\Exception\RequestException;

/**
 * Thrown if a parameter is missing in the request.
 */
class MissingParameterException extends RequestException
{
}
