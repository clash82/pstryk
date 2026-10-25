<?php declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Security\LoginThrottle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class LoginThrottleTest extends TestCase
{
    private LoginThrottle $throttle;

    protected function setUp(): void
    {
        $this->throttle = new LoginThrottle(
            new ArrayAdapter(),
            new ParameterBag(['app' => ['login_throttle' => ['max_attempts' => 3, 'lockout_minutes' => 10]]])
        );
    }

    public function testLoginIsAvailableBeforeLimitIsReached(): void
    {
        $this->throttle->registerFailure('127.0.0.1');
        $this->throttle->registerFailure('127.0.0.1');

        $this->assertSame(0, $this->throttle->getRemainingLockoutSeconds('127.0.0.1'));
    }

    public function testLoginIsBlockedAfterLimitIsReached(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $this->throttle->registerFailure('127.0.0.1');
        }

        $remaining = $this->throttle->getRemainingLockoutSeconds('127.0.0.1');

        $this->assertGreaterThan(0, $remaining);
        $this->assertLessThanOrEqual(600, $remaining);
        $this->assertSame(0, $this->throttle->getRemainingLockoutSeconds('127.0.0.2'));
    }

    public function testResetUnblocksLogin(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $this->throttle->registerFailure('127.0.0.1');
        }

        $this->throttle->reset('127.0.0.1');

        $this->assertSame(0, $this->throttle->getRemainingLockoutSeconds('127.0.0.1'));
    }
}
