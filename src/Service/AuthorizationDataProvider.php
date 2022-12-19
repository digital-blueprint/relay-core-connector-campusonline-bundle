<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorCampusonlineBundle\Service;

use Dbp\Relay\CoreBundle\Authorization\AuthorizationDataProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class AuthorizationDataProvider implements AuthorizationDataProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var CacheInterface
     */
    private $cachePool;

    /** @var int */
    private $cacheTTL;

    private $config;

    /**
     * @var OrganizationDataProvider
     */
    private $orgProv;

    public function __construct(OrganizationDataProvider $orgProv)
    {
        $this->config = [];
        $this->logger = new NullLogger();
        $this->orgProv = $orgProv;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function setCache(?CacheInterface $cachePool, int $ttl)
    {
        $this->cachePool = $cachePool;
        $this->cacheTTL = $ttl;
    }

    public function getAvailableAttributes(): array
    {
        $names = [];
        $orgIds = $this->config['organization_ids'] ?? [];
        foreach ($orgIds as $org) {
            $names[] = $org['name'];
        }

        return $names;
    }

    public function _getUserAttributes(): array
    {
        $attrs = [];
        $orgIds = $this->config['organization_ids'] ?? [];
        foreach ($orgIds as $org) {
            $name = $org['name'];
            $rootId = $org['root_id'];
            $filter = $org['filter'] ?? null;
            $attrs[$name] = $this->orgProv->getIds($rootId, $filter);
        }

        return $attrs;
    }

    public function getUserAttributes(?string $userIdentifier): array
    {
        if ($this->cachePool !== null) {
            return $this->cachePool->get('all_attributes', function (ItemInterface $item) {
                $item->expiresAfter($this->cacheTTL);

                return $this->_getUserAttributes();
            });
        }

        return $this->_getUserAttributes();
    }
}
