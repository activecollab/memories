<?php

/*
 * This file is part of the Active Collab Memories.
 *
 * (c) A51 doo <info@activecollab.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ActiveCollab\Memories;

use ActiveCollab\Memories\Adapter\Adapter;
use InvalidArgumentException;

/**
 * @package ActiveCollab
 */
final class Memories
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Return value that is stored under the key. If value is not found, $if_not_found_return should be returned.
     *
     * Set $use_cache to false if you want this method to ignore cached values
     *
     * @param  string     $key
     * @param  mixed|null $if_not_found_return
     * @param  bool       $use_cache
     * @return mixed
     */
    public function get($key, $if_not_found_return = null, $use_cache = true)
    {
        $key = trim($key);

        if ($key) {
            $value = $this->adapter->read([$key], $use_cache)[$key];

            if ($value === null && $if_not_found_return !== null) {
                $value = $if_not_found_return;
            }

            return $value;
        } else {
            throw new InvalidArgumentException('Key is required');
        }
    }

    /**
     * Set a value for the given key.
     *
     * @param  string                   $key
     * @param  mixed                    $value
     * @param  bool                     $bulk
     * @return array
     * @throws InvalidArgumentException
     */
    public function set($key, $value = null, $bulk = false)
    {
        if (strpos($key, '[') !== false || strpos($key, ']') !== false) {
            throw new InvalidArgumentException("Characters [ and ] can't be used in keys");
        }

        return $this->adapter->write([trim($key) => $value], $bulk);
    }

    /**
     * Forget a value that we have stored under the $key.
     *
     * @param string     $key
     * @param bool|false $bulk
     */
    public function forget($key, $bulk = false)
    {
        $this->adapter->delete([trim($key)], $bulk);
    }
}
