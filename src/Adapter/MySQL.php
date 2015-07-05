<?php

  namespace ActiveCollab\Memories\Adapter;

  /**
   * @package ActiveCollab\Memories\Adapter
   */
  class MySQL implements Adapter
  {
    const TABLE_NAME = 'memories';

    /**
     * @var \MySQLi
     */
    private $link;

    /**
     * @param \MySQLi $link
     */
    public function __construct(\MySQLi &$link)
    {
      $this->link = $link;

      $this->query("CREATE TABLE IF NOT EXISTS `memories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
        `value` mediumtext COLLATE utf8mb4_unicode_ci,
        `updated_on` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `key` (`key`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * @param  string[]   $keys
     * @param  bool|false $use_cache
     * @return mixed[]
     */
    public function read(array $keys, $use_cache = false)
    {
      $result = array_fill_keys($keys, null);

      if ($rows = $this->query('SELECT `key`, `value` FROM memories WHERE `key` IN (' . $this->escapeKeys($keys) . ')')) {
        while ($row = $rows->fetch_assoc()) {
          $result[$row['key']] = unserialize($row['value']);
        }
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
      $to_delete = [];

      foreach ($key_value as $key => $value) {
        if ($value === null) {
          $to_delete[] = $key;
        } else {
          $this->query('INSERT INTO `memories` (`key`, `value`) VALUES ("' . $this->link->escape_string($key) . '", "' . $this->link->escape_string(serialize($value)) . '")');
        }
      }

      if (!empty($to_delete)) {
        $this->delete($to_delete);
      }
    }

    /**
     * @param string[] $keys
     * @param boolean  $bulk
     */
    public function delete(array $keys, $bulk = false)
    {
      $this->query('DELETE FROM `memories` WHERE `key` IN (' . $this->escapeKeys($keys) . ')');
    }

    private function query($sql)
    {
      $query_result = $this->link->query($sql);

      // Handle query error
      if ($query_result === false && $this->link->errno) {
        throw new \Exception($this->link->error . '. SQL: ' . $sql);
      }

      return $query_result;
    }

    private function escapeKeys(array $keys)
    {
      return implode(', ', array_map(function($key) {
        return "'" . $this->link->escape_string($key) . "'";
      }, $keys));
    }
  }