<?php

/*
 * This file is part of the Active Collab Memories project.
 *
 * (c) A51 doo <info@activecollab.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ActiveCollab\Memories\Test\Fixtures;

use ActiveCollab\Memories\Adapter\AdapterInterface;

/**
 * @package ActiveCollab\Memories\Adapter
 */
class TestAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param  string[]   $keys
     * @param  bool|false $use_cache
     * @return mixed[]
     */
    public function read(array $keys, $use_cache = false)
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = array_key_exists($key, $this->data) ? $this->data[$key] : null;
        }

        return $result;
    }

    /**
     * @param  array $key_value
     * @param  bool  $bulk
     * @return array
     */
    public function write(array $key_value, $bulk = false)
    {
        foreach ($key_value as $key => $value) {
            if ($value === null) {
                unset($this->data[$key]);
            } else {
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * @param string[] $keys
     * @param bool     $bulk
     */
    public function delete(array $keys, $bulk = false)
    {
        foreach ($keys as $name) {
            unset($this->data[$name]);
        }
    }
}
