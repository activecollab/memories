<?php

  namespace ActiveCollab\Memories\Adapter;

  /**
   * @package ActiveCollab\Memories\Adapter
   */
  interface Adapter
  {
    /**
     * @param  string[]   $keys
     * @param  bool|false $use_cache
     * @return mixed[]
     */
    public function read(array $keys, $use_cache = false);

    /**
     * @param  array   $key_value
     * @param  boolean $bulk
     * @return array
     */
    public function write(array $key_value, $bulk = false);

    /**
     * @param string[] $keys
     * @param boolean  $bulk
     */
    public function delete(array $keys, $bulk = false);
  }