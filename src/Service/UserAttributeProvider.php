<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorCampusonlineBundle\Service;

use Dbp\Relay\CoreBundle\User\UserAttributeException;
use Dbp\Relay\CoreBundle\User\UserAttributeProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class UserAttributeProvider implements UserAttributeProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ?CacheInterface $cachePool = null;
    private int $cacheTTL = 0;
    private array $config = [];

    public function __construct(
        private readonly OrganizationDataProvider $organizationDataProvider)
    {
        $this->config = [];
        $this->logger = new NullLogger();
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function setCache(?CacheInterface $cachePool, int $ttl): void
    {
        $this->cachePool = $cachePool;
        $this->cacheTTL = $ttl;
    }

    public function hasUserAttribute(string $name): bool
    {
        foreach ($this->config['organization_ids'] ?? [] as $orgAttributeConfig) {
            if ($name === $orgAttributeConfig['name']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws UserAttributeException
     */
    public function getUserAttribute(?string $userIdentifier, string $name): mixed
    {
        if ($this->cachePool !== null) {
            return $this->cachePool->get($name, function (ItemInterface $item) use ($name) {
                $item->expiresAfter($this->cacheTTL);

                return $this->getUserAttributeInternal($name);
            });
        }

        return $this->getUserAttributeInternal($name);
    }

    /**
     * @throws UserAttributeException
     */
    public function getUserAttributeInternal(string $name): array
    {
        foreach ($this->config['organization_ids'] ?? [] as $orgAttributeConfig) {
            if ($name === $orgAttributeConfig['name']) {
                return $this->organizationDataProvider->getIds(
                    $orgAttributeConfig['root_id'], $orgAttributeConfig['filter'] ?? null);
            }
        }

        throw new UserAttributeException("User attribute '$name' undefined",
            UserAttributeException::USER_ATTRIBUTE_UNDEFINED);
    }
}
