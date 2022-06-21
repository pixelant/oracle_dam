<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Domain\Repository;

class AssetRepository extends AbstractOracleDamRepository
{
    /**
     * @param string $id
     *
     * @return array
     */
    public function findById( string $id ) {
        return $this->api->content()->retrieveContent($id);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function downloadByUrl( string $url ) {
        return $this->api->getAuthenticatedUrl($url);
    }
}
