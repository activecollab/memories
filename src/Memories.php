<?php

/*
 * This file is part of the Active Collab Memories project.
 *
 * (c) A51 doo <info@activecollab.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ActiveCollab\Memories;

use ActiveCollab\Memories\Adapter\AdapterInterface;
use InvalidArgumentException;

/**
 * @package ActiveCollab
 */
final class Memories implements MemoriesInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function set($key, $value = null, $bulk = false)
    {
        if (strpos($key, '[') !== false || strpos($key, ']') !== false) {
            throw new InvalidArgumentException("Characters [ and ] can't be used in keys");
        }

        return $this->adapter->write([trim($key) => $value], $bulk);
    }

    /**
     * {@inheritdoc}
     */
    public function forget($key, $bulk = false)
    {
        $this->adapter->delete([trim($key)], $bulk);
    }
}
