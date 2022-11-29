<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorCampusonlineBundle\Service;

use Dbp\Relay\CoreBundle\Authorization\AuthorizationDataProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class AuthorizationDataProvider implements AuthorizationDataProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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

    public function getAvailableAttributes(): array
    {
        $names = [];
        $orgIds = $this->config['organization_ids'] ?? [];
        foreach ($orgIds as $org) {
            $names[] = $org['name'];
        }

        return $names;
    }

    public function getUserAttributes(?string $userIdentifier): array
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
}
