<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Api\Controller;

class ContentDeliveryController extends AbstractController
{
    /**
     * @param string $id
     *
     * @return array
     */
    public function retrieveContent(string $id): array
    {
        return $this->getContentItem($id);
    }
}
