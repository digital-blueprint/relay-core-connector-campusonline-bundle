<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorCampusonlineBundle\Service;

use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitData;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class OrganizationDataProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /** @var int */
    private $cacheTTL;

    private $config;

    public function __construct()
    {
        $this->config = [];
        $this->logger = new NullLogger();
    }

    public function setCache(?CacheItemPoolInterface $cachePool, int $ttl)
    {
        $this->cachePool = $cachePool;
        $this->cacheTTL = $ttl;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    private function getApi(string $rootOrgUnitId): Api
    {
        $baseUrl = $this->config['api_url'] ?? '';
        $accessToken = $this->config['api_token'] ?? '';

        return new Api($baseUrl, $accessToken, $rootOrgUnitId,
            $this->logger, $this->cachePool, $this->cacheTTL);
    }

    /**
     * Returns a list of all origanization IDs including and below the passed ID.
     *
     * @return string[]
     */
    public function getIds(string $rootOrgUnitId): array
    {
        $api = $this->getApi($rootOrgUnitId);
        /**
         * @var OrganizationUnitData[] $items
         */
        $items = $api->OrganizationUnit()->getOrganizationUnits()->getItems();
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $item->getIdentifier();
        }

        return $ids;
    }
}
