<?php

  namespace ActiveCollab\Memories\Test;

  use ActiveCollab\Memories\Memories;
  use ActiveCollab\Memories\Adapter\MySQL as MySQLAdapter;

  /**
   * Test memories
   */
  class MySQLAdapterTest extends TestCase
  {
    /**
     * @var Memories
     */
    private $memories;

    /**
     * @var \MySQLi
     */
    private $link;

    /**
     * Set up before each test
     */
    public function setUp()
    {
      parent::setUp();

      $this->link = new \MySQLi('localhost', 'root', '', 'activecollab_memories_test');
      $this->link->query('DROP TABLE IF EXISTS `' . MySQLAdapter::TABLE_NAME . '`');

      $adapter = new MySQLAdapter($this->link);
      $this->memories = new Memories($adapter);
    }

    /**
     * Check if we are starting with an empty database
     */
    public function testEmptyTable()
    {
      $this->assertRecordsCount(0);
    }

    /**
     * Test set inserts records
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
     * Test update of existing key
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
     * Test if not found returns a valid value when value is not found
     */
    public function testIfNotFoundReturnForNullValues()
    {
      $this->assertNull($this->memories->get('Unknown key #1'));
      $this->assertEquals(12, $this->memories->get('Unknown key #2', 12));
    }

    /**
     * Test if system works properly when there is a value, but it is empty
     */
    public function testIfNotFoundReturnIgnoresNonNullValues()
    {
      $this->memories->set('Known key', 0);
      $this->assertEquals(0, $this->memories->get('Known key', 12));

      $this->memories->set('Known key', null);
      $this->assertEquals(12, $this->memories->get('Known key', 12));
    }

    /**
     * Test forget
     */
    public function testForget()
    {
      $this->memories->set('Known key', 13);
      $this->assertEquals(13, $this->memories->get('Known key'));

      $this->memories->forget('Known key');

      $this->assertNull($this->memories->get('Known key'));
    }

    /**
     * Check number of records in memories table
     *
     * @param integer $expected
     */
    private function assertRecordsCount($expected)
    {
      $result = $this->link->query('SELECT COUNT(`id`) AS "record_count" FROM `' . MySQLAdapter::TABLE_NAME . '`');
      $this->assertEquals(1, $result->num_rows);
      $this->assertEquals($expected, (integer) $result->fetch_assoc()['record_count']);
    }
  }