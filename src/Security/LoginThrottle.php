<?php declare(strict_types=1);

namespace App\Security;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Counts failed logins per client IP address and blocks further attempts for a while
 * (see "login_throttle" in config/packages/app.yaml).
 */
class LoginThrottle
{
    private readonly int $maxAttempts;
    private readonly int $lockoutSeconds;

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        ParameterBagInterface $parameterBag
    ) {
        $config = (array) $parameterBag->get('app')['login_throttle'];

        $this->maxAttempts = (int) $config['max_attempts'];
        $this->lockoutSeconds = (int) $config['lockout_minutes'] * 60;
    }

    /**
     * Returns the number of seconds the login stays blocked for, or 0 if it is available.
     */
    public function getRemainingLockoutSeconds(string $clientIp): int
    {
        $item = $this->cache->getItem($this->getKey($clientIp));

        if (!$item->isHit()) {
            return 0;
        }

        $state = (array) $item->get();

        if ((int) $state['count'] < $this->maxAttempts) {
            return 0;
        }

        return max(0, (int) $state['expires'] - time());
    }

    public function registerFailure(string $clientIp): void
    {
        $item = $this->cache->getItem($this->getKey($clientIp));
        $now = time();

        $state = $item->isHit() ? (array) $item->get() : ['count' => 0, 'expires' => $now + $this->lockoutSeconds];
        ++$state['count'];

        // reaching the limit starts the lockout period, which is counted from the last failed attempt
        if ($state['count'] >= $this->maxAttempts) {
            $state['expires'] = $now + $this->lockoutSeconds;
        }

        $item->set($state);
        $item->expiresAfter(max(1, $state['expires'] - $now));
        $this->cache->save($item);
    }

    public function reset(string $clientIp): void
    {
        $this->cache->deleteItem($this->getKey($clientIp));
    }

    private function getKey(string $clientIp): string
    {
        return 'login_throttle_'.hash('sha256', $clientIp);
    }
}
