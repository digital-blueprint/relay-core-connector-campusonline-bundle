<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorCampusonlineBundle\Service;

use Dbp\CampusonlineApi\PublicRestApi\Connection;
use Dbp\CampusonlineApi\PublicRestApi\Organizations\OrganizationApi;
use Dbp\CampusonlineApi\PublicRestApi\Organizations\OrganizationResource;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class OrganizationDataProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ?CacheInterface $cachePool = null;
    private int $cacheTTL = 0;
    private array $config = [];

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function setCache(?CacheInterface $cachePool, int $ttl): void
    {
        $this->cachePool = $cachePool;
        $this->cacheTTL = $ttl;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return OrganizationResource[]
     */
    private function getAllOrganizations(): array
    {
        $api = $this->getApi();
        $cursor = null;

        $items = [];
        while (true) {
            $page = $api->getOrganizationsCursorBased(
                ['only_active' => 'true', 'exclude_virtual' => 'true'],
                cursor: $cursor, maxNumItems: 1000);
            $items = array_merge($items, iterator_to_array($page->getResources()));
            $cursor = $page->getNextCursor();
            if ($cursor === null) {
                break;
            }
        }

        return $items;
    }

    /**
     * Returns a list of all organization IDs including and below the passed ID.
     *
     * @return string[]
     */
    public function getIds(?string $filter): array
    {
        $expressionLanguage = new ExpressionLanguage();

        if ($this->cachePool !== null) {
            $items = $this->cachePool->get('ALL', function (ItemInterface $item) {
                $item->expiresAfter($this->cacheTTL);

                return $this->getAllOrganizations();
            });
        } else {
            $items = $this->getAllOrganizations();
        }

        $ids = [];
        foreach ($items as $item) {
            if ($filter !== null) {
                if ($expressionLanguage->evaluate($filter, ['org' => $item]) === false) {
                    continue;
                }
            }
            $ids[] = $item->getUid();
        }

        return $ids;
    }

    private function getApi(): OrganizationApi
    {
        $baseUrl = $this->config['base_url'] ?? '';
        $clientId = $this->config['client_id'] ?? '';
        $clientSecret = $this->config['client_secret'] ?? '';

        $connection = new Connection($baseUrl, $clientId, $clientSecret);
        $connection->setLogger($this->logger);

        return new OrganizationApi($connection);
    }
}
