<?php

  namespace ActiveCollab\Memories\Adapter;

  /**
   * @package ActiveCollab\Memories\Adapter
   */
  class Test implements Adapter
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
     * @param  array   $key_value
     * @param  boolean $bulk
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
     * @param boolean  $bulk
     */
    public function delete(array $keys, $bulk = false)
    {
      foreach ($keys as $name) {
        unset($this->data[$name]);
      }
    }
  }