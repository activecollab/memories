<?php

  namespace ActiveCollab\Memories\Test;

  use ActiveCollab\Memories\Memories;
  use InvalidArgumentException;
  use ActiveCollab\Memories\Adapter\Test as TestAdapter;

  /**
   * Test memories
   */
  class MemoriesTest extends TestCase
  {
    /**
     * @var Memories
     */
    private $memories;

    /**
     * Set up before each test
     */
    public function setUp()
    {
      parent::setUp();

      $adapter = new TestAdapter();
      $this->memories = new Memories($adapter);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLeftSquareBracketsThrowAnException()
    {
      $this->memories->set('[Something Antoher Thing', 123);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRightSquareBracketsThrowAnException()
    {
      $this->memories->set(']Something Antoher Thing', 123);
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
  }