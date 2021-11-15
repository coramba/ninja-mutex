<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Lock;

use Predis;

/**
 * Lock implementor using Predis (client library for Redis)
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class PredisRedisLock extends LockAbstract
{
    /**
     * Predis connection
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
     * @param $client Predis\Client
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
			$this->client->expire($name, $this->expiration);
		}

        return true;
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
        return null !== $this->client->get($name);
    }
}
