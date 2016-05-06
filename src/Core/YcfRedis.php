<?php

namespace Ycf\Core;

/**
 * Simple wrapper class on phpredis extension
 * refer to: https://github.com/alxmsl/Redis
 *
 */
class YcfRedis
{
    /**
     * @var string redis instance hostname
     */
    private $host = '';

    /**
     * @var int redis instance port
     */
    private $port = -1;

    /**
     * @var float redis instance connect timeout
     */
    private $connectTimeout = 0;

    /**
     * @var int number of tries for connect to redis instance
     */
    private $connectTries = 1;

    /**
     * @var bool use persistence connection, or not
     */
    private $persistent = false;

    /**
     * @var \Redis phpredis object instance
     */
    private $Redis = null;

    public function __construct(array $config)
    {
        isset($config['host']) && $this->host                     = $config['host'];
        isset($config['port']) && $this->port                     = $config['port'];
        isset($config['connectTimeout']) && $this->connectTimeout = $config['connectTimeout'];
        isset($config['connectTries']) && $this->connectTries     = $config['connectTries'];
    }

    /**
     * Getter of phpredis object
     * @return \Redis phpredis object instance
     * @throws RedisNotConfiguredException if any of required redis connect parameters are loose
     */
    private function getRedis()
    {
        if (is_null($this->Redis)) {
            if ($this->isConfigured()) {
                $this->Redis = new \Redis();
                $this->reconnect();
            } else {
                throw new RedisNotConfiguredException();
            }
        }
        return $this->Redis;
    }

    /**
     * Check required connection parameters configuration method
     * @return bool check result
     */
    private function isConfigured()
    {
        return !empty($this->host) && $this->port >= 0 && $this->port <= 65535;
    }

    /**
     * Reconnect to the redis instance
     * @return bool connection result. Always true.
     * @throws RedisConnectException if connection could not established by RedisException cause
     * @throws RedisTriesOverConnectException if connection could not established because tries was over
     */
    private function reconnect()
    {
        $count = 0;
        do {
            $count += 1;
            try {
                if ($this->persistent) {
                    $result = $this->Redis->pconnect($this->host, $this->port, $this->connectTimeout);
                } else {
                    $result = $this->Redis->connect($this->host, $this->port, $this->connectTimeout);
                }
            } catch (\RedisException $ex) {
                throw new RedisConnectException();
            }
            if (true === $result) {
                return true;
            }
        } while ($count < $this->connectTries);

        $this->Redis = null;
        throw new RedisTriesOverConnectException();
    }

    /**
     * Setter of connection timeout parameter
     * @param float $connectTimeout connection timeout value
     * @throws \InvalidArgumentException
     * @return Redis self
     */
    public function setConnectTimeout($connectTimeout)
    {
        $this->connectTimeout = (float) $connectTimeout;
        if ($this->connectTimeout < 0) {
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    /**
     * Getter of connection timeout exception
     * @return float connect timeout value
     */
    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    /**
     * Setter of number of connection tries
     * @param int $connectTries connection tries count
     * @throws \InvalidArgumentException
     * @return Redis self
     */
    public function setConnectTries($connectTries)
    {
        $this->connectTries = (int) $connectTries;
        if ($this->connectTries < 1) {
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    /**
     * Getter of number of connection tries
     * @return int connection tries count
     */
    public function getConnectTries()
    {
        return $this->connectTries;
    }

    /**
     * Setter for redis instance hostname or ip address
     * @param string $host hostname or ip address
     * @throws \InvalidArgumentException
     * @return Redis self
     */
    public function setHost($host)
    {
        $this->host = (string) $host;
        if (empty($this->host)) {
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    /**
     * Getter of redis instance hostname
     * @return string redis instance hostname or ip address
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Setter of redis instance connection port
     * @param int $port redis instance connection port
     * @throws \InvalidArgumentException
     * @return Redis self
     */
    public function setPort($port)
    {
        $this->port = $port;
        if ($this->port < 0 || $this->port > 65535) {
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    /**
     * Getter of redis instance connection port
     * @return int redis instance connection port
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Use persistent connection or not
     * @param bool $persistent if is set to true, pconnect will use, overwise not
     * @return Redis self
     */
    public function setPersistent($persistent)
    {
        $this->persistent = (bool) $persistent;
        return $this;
    }

    /**
     * Use persistent connection or not
     * @return bool if is set to true, pconnect will use, overwise not
     */
    public function getPersistent()
    {
        return $this->persistent;
    }

    /*
     * phpredis interface implementation
     */

    /**
     * Increment key value
     * @param string $key key
     * @param int $value value for increment
     * @return int current value
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function incr($key, $value = 1)
    {
        $value = (int) $value;
        try {
            $result = ($value > 1)
            ? $this->getRedis()->incrBy($key, $value)
            : $this->getRedis()->incr($key);
            if (false !== $result) {
                return $result;
            }
            throw new RedisImpossibleValueException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Decrement key value
     * @param string $key key
     * @param int $value value for increment
     * @return int current value
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function decr($key, $value = 1)
    {
        $value = (int) $value;
        try {
            $result = ($value > 1)
            ? $this->getRedis()->decrBy($key, $value)
            : $this->getRedis()->decr($key);
            if (false !== $result) {
                return $result;
            }
            throw new RedisImpossibleValueException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Append string value
     * @param string $key key
     * @param string $value appended value
     * @return int length of a key after append
     * @throws RedisConnectException
     */
    public function append($key, $value)
    {
        try {
            $result = $this->getRedis()->append($key, $value);
            if (false !== $result) {
                return $result;
            }
            throw new RedisImpossibleValueException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Get key value
     * @param string $key key
     * @return mixed key value
     * @throws RedisConnectException exception on connection to redis instance
     * @throws RedisKeyNotFoundException when key not found
     */
    public function get($key)
    {
        try {
            $result = $this->getRedis()->get($key);
            if (false === $result) {
                throw new RedisKeyNotFoundException();
            }
            return $result;
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Get multiple keys values
     * @param array $keys keys
     * @return array values
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function mget(array $keys)
    {
        try {
            $result = $this->getRedis()->mGet($keys);
            if (false !== $result) {
                return array_combine($keys, $result);
            }
            throw new RedisImpossibleValueException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Set key value
     * @param string $key key
     * @param mixed $value value
     * @param int $timeout ttl timeout in milliseconds
     * @return bool operation result
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function set($key, $value, $timeout = 0)
    {
        try {
            $result = (0 == $timeout)
            ? $this->getRedis()->set($key, $value)
            : $this->getRedis()->psetex($key, $timeout, $value);
            if (false !== $result) {
                return $result;
            }
            throw new RedisImpossibleValueException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Set multiple key values
     * @param array $values key and values
     * @return bool operation result
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function mset(array $values)
    {
        try {
            return $this->getRedis()->mset($values);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Set key value if not exists
     * @param string $key key
     * @param mixed $value value
     * @return bool returns true, if operation complete succesfull, else false
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function setnx($key, $value)
    {
        try {
            return $this->getRedis()->setnx($key, $value);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Set multiple key values
     * @param array $values key and values
     * @return bool operation result
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function msetnx(array $values)
    {
        try {
            return $this->getRedis()->msetnx($values);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * GetSet implementation
     * @param string $key key
     * @param mixed $value value
     * @return bool|mixed previous value of a key. If key did not set, method returns false
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function getset($key, $value)
    {
        try {
            return $this->getRedis()->getSet($key, $value);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Delete key or keys
     * @param string|array $keys key or keys array
     * @return int count of deleted keys
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function delete($keys)
    {
        try {
            $result = $this->getRedis()->delete($keys);
            if (false !== $result) {
                return $result;
            }
            throw new RedisImpossibleValueException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Check if key exists
     * @param string $key key
     * @return bool check result
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function exists($key)
    {
        try {
            return $this->getRedis()->exists($key);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Rename key
     * @param string $source current key name
     * @param string $destination needed key name
     * @return bool operation result. If false, source key not found
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function rename($source, $destination)
    {
        try {
            return $this->getRedis()->rename($source, $destination);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Rename key if needed key name was not
     * @param string $source current key name
     * @param string $destination needed key name
     * @return bool operation result. If false, source key not found or needed key name found
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function renamenx($source, $destination)
    {
        try {
            return $this->getRedis()->renamenx($source, $destination);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Get string length of a key
     * @param string $key key
     * @return int key value length
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function strlen($key)
    {
        try {
            $result = $this->getRedis()->strlen($key);
            if (false !== $result) {
                return $result;
            }
            throw new RedisImpossibleValueException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Set ttl for a key
     * @param string $key key
     * @param int $timeout ttl in milliseconds
     * @return bool operation result. If false ttl cound not be set, or key not found
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function expire($key, $timeout)
    {
        try {
            return $this->getRedis()->pexpire($key, $timeout);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Set time of life for the key
     * @param string $key key
     * @param int $timestamp unix timestamp of time of death
     * @return bool operation result. If false timestamp cound not be set, or key not found
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function expireat($key, $timestamp)
    {
        try {
            return $this->getRedis()->expireat($key, $timestamp);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Get ttl of the key
     * @param string $key key
     * @return int|bool ttl in milliseconds or false, if ttl is not set or key not found
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function ttl($key)
    {
        try {
            $result = $this->getRedis()->pttl($key);
            return (-1 != $result) ? $result : false;
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Remove ttl from the key
     * @param string $key key
     * @return bool if true ttl was removed successful, if false ttl did not set, or key not found
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function persist($key)
    {
        try {
            return $this->getRedis()->persist($key);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Get key bit
     * @param string $key key
     * @param int $offset bit offset
     * @return int bit value at the offset
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function getbit($key, $offset)
    {
        $offset = (int) $offset;
        try {
            $result = $this->getRedis()->getBit($key, $offset);
            if (false !== $result) {
                return $result;
            }
            throw new RedisImpossibleValueException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Set key bit
     * @param string $key key
     * @param int $offset bit offset
     * @param int $value bit value. May be 0 or 1
     * @return int bit value before operation complete
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function setbit($key, $offset, $value)
    {
        $offset = (int) $offset;
        $value  = (int) (bool) $value;
        try {
            $result = $this->getRedis()->setBit($key, $offset, $value);
            if (false !== $result) {
                return $result;
            }
            throw new RedisImpossibleValueException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Evaluate Lua code
     * @param string $code string of Lua code
     * @param array $arguments array of Lua script arguments
     * @return mixed code execution result
     * @throws RedisConnectException exception on connection to redis instance
     * @throws RedisScriptExecutionException when script execution faled
     */
    public function evaluate($code, array $arguments = array())
    {
        try {
            if (empty($arguments)) {
                $result = $this->getRedis()->eval($code);
            } else {
                $result = $this->getRedis()->eval($code, $arguments, count($arguments));
            }

            $lastError = $this->getRedis()->getLastError();
            $this->getRedis()->clearLastError();
            if (is_null($lastError)) {
                return $result;
            }
            throw new RedisScriptExecutionException($lastError);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Evaluate Lua code by hash
     * @param string $sha SHA1 string of Lua code
     * @param array $arguments array of Lua script arguments
     * @return mixed code execution result
     * @throws RedisConnectException exception on connection to redis instance
     * @throws RedisScriptExecutionException when script execution faled
     */
    public function evalSha($sha, array $arguments = array())
    {
        try {
            if (empty($arguments)) {
                $result = $this->getRedis()->evalSha($sha);
            } else {
                $result = $this->getRedis()->evalSha($sha, $arguments, count($arguments));
            }

            $lastError = $this->getRedis()->getLastError();
            $this->getRedis()->clearLastError();
            if (is_null($lastError)) {
                return $result;
            }
            throw new RedisScriptExecutionException($lastError);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Add member to the set
     * @param string $key key
     * @param mixed $member set member
     * @return int count of added members
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function sadd($key, $member)
    {
        try {
            $result = $this->getRedis()->sAdd($key, $member);
            if (false !== $result) {
                return $result;
            }
            throw new RedisImpossibleValueException();
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Pop (remove and return) a random member from the set
     * @param string $key key
     * @return mixed set member
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function spop($key)
    {
        try {
            return $this->getRedis()->sPop($key);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Return random member from the set
     * @param string $key key
     * @return mixed set member
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function srandmember($key)
    {
        try {
            return $this->getRedis()->sRandMember($key);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Returns size of the set
     * @param string $key set
     * @return int members count of the set
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function scard($key)
    {
        try {
            return $this->getRedis()->sCard($key);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Check that member is a member of the set
     * @param string $key key
     * @param mixed $member member
     * @return bool check result
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function sismembers($key, $member)
    {
        try {
            return $this->getRedis()->sIsMember($key);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Returns all members of the set
     * @param string $key key
     * @return array all members of the set
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function smembers($key)
    {
        try {
            return $this->getRedis()->sMembers($key);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Remove member from the set
     * @param string $key key
     * @param mixed $member set member
     * @return int count of removed elements
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function srem($key, $member)
    {
        try {
            return $this->getRedis()->sRem($key);
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }

    /**
     * Create difference set
     * @param string $destination key for result set
     * @param array $sources source keys
     * @return int size of result set
     * @throws RedisConnectException exception on connection to redis instance
     */
    public function sdiffstore($destination, array $sources)
    {
        try {
            return call_user_func_array(array(
                $this->getRedis(),
                'sDiffStore',
            ), array_merge(array($destination), $sources));
        } catch (\RedisException $ex) {
            throw new RedisConnectException();
        }
    }
}

class PhpRedisException extends \Exception
{}
class RedisConnectException extends PhpRedisException
{}
final class RedisTriesOverConnectException extends RedisConnectException
{}
final class RedisNotConfiguredException extends PhpRedisException
{}
final class RedisKeyNotFoundException extends PhpRedisException
{}
final class RedisScriptExecutionException extends PhpRedisException
{}
final class RedisImpossibleValueException extends PhpRedisException
{}
