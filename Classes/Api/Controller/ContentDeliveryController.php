<?php

namespace Oracle\Typo3Dam\Api\Controller;

class ContentDeliveryController extends AbstractController
{
    /**
     * @param string $id
     *
     * @return array
     */
    public function retrieveContent(string $id)
    {
        return $this->getContentItem($id);
    }
}
