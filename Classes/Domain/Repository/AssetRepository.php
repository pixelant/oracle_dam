<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Domain\Repository;

use GuzzleHttp\Exception\ClientException;

class AssetRepository extends AbstractOracleDamRepository
{
    /**
     * @var array Key is the media ID
     */
    protected static $assetCache = [];

    /**
     * @param string $id
     * @return array|null
     * @throws ClientException
     */
    public function findById(string $id): ?array
    {
        if (isset(self::$assetCache[$id])) {
            return self::$assetCache[$id];
        }

        try {
            $data = $this->api->contentManagement()->getItem($id);
        } catch (ClientException $exception) {
            if ($exception->getCode() === 404) {
                self::$assetCache[$id] = null;

                return null;
            }

            throw $exception;
        }

        $rendition = array_search(
            'Large',
            array_column($data['fields']['renditions'], 'name')
        );

        $format = array_search(
            'jpg',
            array_column($data['fields']['renditions'][$rendition]['formats'], 'format')
        );

        self::$assetCache[$id] = [
            'id' => $data['id'],
            'version' => $data['version'],
            'name' => $data['name'],
            'title' => $data['fields']['title'] ?? '',
            'alternate_text' => $data['fields']['alternate_text'] ?? '',
            'caption' => $data['fields']['caption'] ?? '',
            'url' => $data['fields']['renditions'][$rendition]['formats'][$format]['links'][0]['href'],
        ];

        return self::$assetCache[$id];
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function downloadByUrl(string $url): string
    {
        return $this->api->getAuthenticatedUrl($url);
    }
}
