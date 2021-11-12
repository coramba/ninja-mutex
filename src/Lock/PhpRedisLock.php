<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) leo108 <root@leo108.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Lock;

use Redis;

/**
 * Lock implementor using PHPRedis
 *
 * @author leo108 <root@leo108.com>
 */
class PhpRedisLock extends LockAbstract
{
    /**
     * Redis connection
     *
     * @var
     */
    protected $client;

	/**
	 * Default expiration time
	 * @var int
	 */
	protected $expiration = 0;

    /**
     * @param $client Redis
	 * @param int $expiration Default expiration time for lock
     */
    public function __construct($client, $expiration = FALSE)
    {
        parent::__construct();

        $this->client = $client;

		if ($expiration) {
			$this->expiration = (int)$expiration;
		}
    }

    /**
     * @param  string $name
     * @param  bool   $blocking
     * @return bool
     */
    protected function getLock($name, $blocking)
    {
        if (!$this->client->setnx($name, serialize($this->getLockInformation()))) {
            return false;
        }

		if ($this->expiration) {
			$this->client->expire($name, time() + $this->expiration);
		}

        return true;
    }

	/**
	 * @return int
	 */
	public function getExpiration() {
		return $this->expiration;
	}

	/**
	 * @param int $expiration
	 *
	 * @return PredisRedisLock
	 */
	public function setExpiration( $expiration ) {
		$this->expiration = $expiration;

		return $this;
	}

    /**
     * Release lock
     *
     * @param  string $name name of lock
     * @return bool
     */
    public function releaseLock($name)
    {
        if (isset($this->locks[$name]) && $this->client->del($name)) {
            unset($this->locks[$name]);

            return true;
        }

        return false;
    }

    /**
     * Check if lock is locked
     *
     * @param  string $name name of lock
     * @return bool
     */
    public function isLocked($name)
    {
        return false !== $this->client->get($name);
    }
}
