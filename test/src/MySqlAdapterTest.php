<?php

/*
 * This file is part of the Active Collab Memories project.
 *
 * (c) A51 doo <info@activecollab.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ActiveCollab\Memories\Test;

use ActiveCollab\DatabaseConnection\ConnectionFactory;
use ActiveCollab\Memories\Adapter\MySqlAdapter;
use ActiveCollab\Memories\Memories;
use ActiveCollab\DatabaseConnection\ConnectionInterface;

/**
 * Test memories.
 */
class MySqlAdapterTest extends TestCase
{
    /**
     * @var Memories
     */
    private $memories;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * Set up before each test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->connection = (new ConnectionFactory())->mysqli('localhost', 'root', '', 'activecollab_memories_test', 'utf8mb4');
        $this->connection->dropTable(MySqlAdapter::TABLE_NAME);

        $adapter = new MySqlAdapter($this->connection);
        $this->assertTrue($this->connection->tableExists(MySqlAdapter::TABLE_NAME));

        $this->memories = new Memories($adapter);
    }

    public function tearDown()
    {
        $this->connection->dropTable(MySqlAdapter::TABLE_NAME);
        $this->connection->disconnect();

        parent::tearDown();
    }

    /**
     * Check if we are starting with an empty database.
     */
    public function testEmptyTable()
    {
        $this->assertRecordsCount(0);
    }

    /**
     * Test set inserts records.
     */
    public function testSetInsertsRecords()
    {
        $this->assertRecordsCount(0);

        $this->memories->set('First Key', 123);
        $this->memories->set('Second Key', 456);
        $this->memories->set('Third Key', 789);

        $this->assertRecordsCount(3);
    }

    /**
     * Test update of existing key.
     */
    public function testUpdateOfExistingKey()
    {
        $this->assertRecordsCount(0);

        $this->memories->set('Key', 123);
        $this->memories->set('Key', 456);

        $this->assertRecordsCount(1);

        $this->assertEquals(456, $this->memories->get('Key'));
    }

    /**
     * Test if not found returns a valid value when value is not found.
     */
    public function testIfNotFoundReturnForNullValues()
    {
        $this->assertNull($this->memories->get('Unknown key #1'));
        $this->assertEquals(12, $this->memories->get('Unknown key #2', 12));
    }

    /**
     * Test if system works properly when there is a value, but it is empty.
     */
    public function testIfNotFoundReturnIgnoresNonNullValues()
    {
        $this->memories->set('Known key', 0);
        $this->assertEquals(0, $this->memories->get('Known key', 12));

        $this->memories->set('Known key', null);
        $this->assertEquals(12, $this->memories->get('Known key', 12));
    }

    /**
     * Test forget.
     */
    public function testForget()
    {
        $this->memories->set('Known key', 13);
        $this->assertEquals(13, $this->memories->get('Known key'));

        $this->memories->forget('Known key');

        $this->assertNull($this->memories->get('Known key'));
    }

    /**
     * Check number of records in memories table.
     *
     * @param int $expected
     */
    private function assertRecordsCount($expected)
    {
        $result = $this->connection->executeFirstCell('SELECT COUNT(`id`) AS "row_count" FROM `' . MySqlAdapter::TABLE_NAME . '`');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test if we get correct default value if value field is empty for some reason (unserliaze() would return false).
     */
    public function testDefaultValueWhenUnserializationFails()
    {
        $this->connection->execute('INSERT INTO `' . MySqlAdapter::TABLE_NAME . '` (`key`, `value`, `updated_on`) VALUES ("Key", "", UTC_TIMESTAMP)');
        $this->assertSame(123, $this->memories->get('Key', 123));
    }
}
