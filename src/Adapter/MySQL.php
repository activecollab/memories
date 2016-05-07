<?php

/*
 * This file is part of the Active Collab Memories.
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
class MySQL implements Adapter
{
    const TABLE_NAME = 'memories';

    /**
     * @var \MySQLi
     */
    private $link;

    /**
     * @param \MySQLi   $link
     * @param bool|true $create_table_if_missing
     */
    public function __construct(\MySQLi &$link, $create_table_if_missing = true)
    {
        $this->link = $link;

        if ($create_table_if_missing) {
            $this->query("CREATE TABLE IF NOT EXISTS `memories` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
          `value` mediumtext COLLATE utf8mb4_unicode_ci,
          `updated_on` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `key` (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }
    }

    /**
     * @param  string[]   $keys
     * @param  bool|false $use_cache
     * @return mixed[]
     */
    public function read(array $keys, $use_cache = false)
    {
        if (empty($keys)) {
            return [];
        }

        $result = array_fill_keys($keys, null);

        if ($rows = $this->query('SELECT `key`, `value` FROM `memories` WHERE `key` IN (' . $this->escapeKeys($keys) . ')')) {
            while ($row = $rows->fetch_assoc()) {
                $result[$row['key']] = unserialize($row['value']);
            }
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
        $to_delete = [];

        foreach ($key_value as $key => $value) {
            if ($value === null) {
                $to_delete[] = $key;
            } else {
                if ($this->keyExists($key)) {
                    $this->update($key, $value);
                } else {
                    $this->insert($key, $value);
                }
            }
        }

        $this->delete($to_delete);
    }

    /**
     * Insert a new record into the table.
     *
     * @param string $key
     * @param mixed  $value
     */
    private function insert($key, $value)
    {
        $this->query('INSERT INTO `memories` (`key`, `value`, `updated_on`) VALUES (' . $this->escape($key) . ', ' . $this->escape(serialize($value)) . ', UTC_TIMESTAMP())');
    }

    /**
     * Update an existing key value.
     *
     * @param string $key
     * @param mixed  $value
     */
    private function update($key, $value)
    {
        $this->query('UPDATE `memories` SET `value` = ' . $this->escape(serialize($value)) . ', `updated_on` = UTC_TIMESTAMP() WHERE `key` = ' . $this->escape($key));
    }

    /**
     * @param string[] $keys
     * @param bool     $bulk
     */
    public function delete(array $keys, $bulk = false)
    {
        if (!empty($keys)) {
            $this->query('DELETE FROM `memories` WHERE `key` IN (' . $this->escapeKeys($keys) . ')');
        }
    }

    /**
     * Query database.
     *
     * @param  string              $sql
     * @return bool|\mysqli_result
     * @throws \Exception
     */
    private function query($sql)
    {
        $query_result = $this->link->query($sql);

        // Handle query error
        if ($query_result === false && $this->link->errno) {
            throw new \Exception($this->link->error . '. SQL: ' . $sql);
        }

        return $query_result;
    }

    /**
     * Return true if a memory with $key exists.
     *
     * @param  string $key
     * @return bool
     */
    private function keyExists($key)
    {
        $result = $this->link->query('SELECT COUNT(`id`) AS "record_count" FROM `' . self::TABLE_NAME . '` WHERE `key` = ' . $this->escape($key));

        return $result->num_rows && (integer) $result->fetch_assoc()['record_count'];
    }

    /**
     * Escape array of keys.
     *
     * @param  array  $keys
     * @return string
     */
    private function escapeKeys(array $keys)
    {
        return implode(', ', array_map(function ($key) {
            return $this->escape($key);
        }, $keys));
    }

    /**
     * Escape a string and put it in single quotes.
     *
     * @param  string $value
     * @return string
     */
    private function escape($value)
    {
        return "'" . $this->link->escape_string($value) . "'";
    }
}
