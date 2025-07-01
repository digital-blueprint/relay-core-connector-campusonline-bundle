<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorCampusonlineBundle\Service;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitData;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Response;

class OrganizationDataProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ?CacheItemPoolInterface $cachePool = null;
    private int $cacheTTL = 0;
    private array $config = [];

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function setCache(?CacheItemPoolInterface $cachePool, int $ttl): void
    {
        $this->cachePool = $cachePool;
        $this->cacheTTL = $ttl;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * Returns a list of all organization IDs including and below the passed ID.
     *
     * @return string[]
     */
    public function getIds(string $rootOrgUnitId, ?string $filter): array
    {
        $expressionLanguage = new ExpressionLanguage();

        $api = $this->getApi($rootOrgUnitId);

        try {
            /** @var OrganizationUnitData[] $items */
            $items = $api->OrganizationUnit()->getOrganizationUnits()->getItems();
        } catch (ApiException $exception) {
            throw ApiError::withDetails(Response::HTTP_BAD_GATEWAY, sprintf('Campusonline backend request failed: %s', $exception->getMessage()));
        }

        $ids = [];
        foreach ($items as $item) {
            if ($filter !== null) {
                if ($expressionLanguage->evaluate($filter, ['org' => $item]) === false) {
                    continue;
                }
            }
            $ids[] = $item->getIdentifier();
        }

        return $ids;
    }

    private function getApi(string $rootOrgUnitId): Api
    {
        $baseUrl = $this->config['api_url'] ?? '';
        $accessToken = $this->config['api_token'] ?? '';

        return new Api($baseUrl, $accessToken, $rootOrgUnitId,
            $this->logger, $this->cachePool, $this->cacheTTL);
    }
}
