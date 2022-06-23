<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Domain\Repository;

class AssetRepository extends AbstractOracleDamRepository
{
    /**
     * @var array Key is the media ID
     */
    protected static $assetCache = [];

    /**
     * @param string $id
     *
     * @return array
     */
    public function findById(string $id)
    {
        if (isset(self::$assetCache[$id])) {
            return self::$assetCache[$id];
        }
        $data = $this->api->content()->retrieveContent($id);

        $rendition             = array_search(
            'Large',
            array_column($data['fields']['renditions'], 'name')
        );
        $format                = array_search(
            'jpg',
            array_column($data['fields']['renditions'][$rendition]['formats'], 'format')
        );
        self::$assetCache[$id] = [
            'id'             => $data['id'] ?? '',
            'name'           => $data['name'] ?? '',
            'title'          => $data['fields']['title'] ?? '',
            'alternate_text' => $data['fields']['alternate_text'] ?? '',
            'caption'        => $data['fields']['caption'] ?? '',
            'url'            => $data['fields']['renditions'][$rendition]['formats'][$format]['links'][0]['href'] ?? ''
        ];

        return self::$assetCache[$id];
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function downloadByUrl(string $url)
    {
        return $this->api->getAuthenticatedUrl($url);
    }
}
