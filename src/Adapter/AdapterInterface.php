<?php

/*
 * This file is part of the Active Collab Memories project.
 *
 * (c) A51 doo <info@activecollab.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ActiveCollab\Memories\Adapter;

/**
 * @package ActiveCollab\Memories\Adapter
 */
interface AdapterInterface
{
    /**
     * @param  string[]   $keys
     * @param  bool|false $use_cache
     * @return mixed[]
     */
    public function read(array $keys, $use_cache = false);

    /**
     * @param  array $key_value
     * @param  bool  $bulk
     * @return array
     */
    public function write(array $key_value, $bulk = false);

    /**
     * @param string[] $keys
     * @param bool     $bulk
     */
    public function delete(array $keys, $bulk = false);
}
