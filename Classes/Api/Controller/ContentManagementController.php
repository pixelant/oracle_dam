<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Api\Controller;

class ContentManagementController extends AbstractController
{
    /**
     * @param string $id
     *
     * @return array
     */
    public function getItem(string $id): array
    {
        $response = $this->client->request('GET', '/content/management/api/v1.1/items/' . $id);

        return json_decode($response->getBody()->getContents(), true);
    }
}
