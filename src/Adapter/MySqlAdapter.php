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

use ActiveCollab\DatabaseConnection\ConnectionInterface;

/**
 * @package ActiveCollab\Memories\Adapter
 */
class MySqlAdapter implements AdapterInterface
{
    const TABLE_NAME = 'memories';

    /**
     * @var ConnectionInterface
     */
    private $connection;

    public function __construct(ConnectionInterface $connection, $create_table_if_missing = true)
    {
        $this->connection = $connection;

        if ($create_table_if_missing && !$this->connection->tableExists(self::TABLE_NAME)) {
            $this->connection->execute("CREATE TABLE `" . self::TABLE_NAME . "` (
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
     * {@inheritdoc}
     */
    public function read(array $keys, $use_cache = false)
    {
        if (empty($keys)) {
            return [];
        }

        $result = array_fill_keys($keys, null);

        if ($rows = $this->connection->execute('SELECT `key`, `value` FROM `' . self::TABLE_NAME . '` WHERE `key` IN ?', $keys)) {
            foreach ($rows as $row) {
                $result[$row['key']] = $row['value'] ? unserialize($row['value']) : null;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    private function insert($key, $value)
    {
        $this->connection->execute('INSERT INTO `' . self::TABLE_NAME . '` (`key`, `value`, `updated_on`) VALUES (?, ?, UTC_TIMESTAMP())', $key, serialize($value));
    }

    /**
     * {@inheritdoc}
     */
    private function update($key, $value)
    {
        $this->connection->execute('UPDATE `' . self::TABLE_NAME . '` SET `value` = ?, `updated_on` = UTC_TIMESTAMP() WHERE `key` = ?', serialize($value), $key);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $keys, $bulk = false)
    {
        if (!empty($keys)) {
            $this->connection->execute('DELETE FROM `' . self::TABLE_NAME . '` WHERE `key` IN ?', $keys);
        }
    }

    /**
     * Return true if a memory with $key exists.
     *
     * @param  string $key
     * @return bool
     */
    private function keyExists($key)
    {
        return (bool) $this->connection->count(self::TABLE_NAME, ['`key` = ?', $key]);
    }
}
