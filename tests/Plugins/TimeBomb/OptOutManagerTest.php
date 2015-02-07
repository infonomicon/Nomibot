<?php

namespace Nomibot\Plugins\TimeBomb;

class OptOutManagerTest extends \PHPUnit_Framework_TestCase
{
    private function filePath()
    {
        return __DIR__.'/test_optouts.json';
    }

    public function setUp()
    {
        $data = json_encode(['OptedOut', 'Tester']);
        file_put_contents($this->filePath(), $data);
    }

    public function tearDown()
    {
        unlink($this->filePath());
    }

    public function testContains()
    {
        $optouts = new OptOutManager($this->filePath());

        $this->assertTrue($optouts->contains('OptedOut'));
        $this->assertTrue($optouts->contains('optedout'));
        $this->assertTrue($optouts->contains('tester'));
        $this->assertFalse($optouts->contains('testing'));
    }

    public function testAdd()
    {
        $optouts = new OptOutManager($this->filePath());

        $optouts->add('testing');

        $this->assertTrue($optouts->contains('testing'));
        $this->assertJsonStringEqualsJsonFile($this->filePath(), '["OptedOut", "Tester", "testing"]');
    }

    public function testRemove()
    {
        $optouts = new OptOutManager($this->filePath());

        $optouts->remove('tester');

        $this->assertFalse($optouts->contains('tester'));
        $this->assertJsonStringEqualsJsonFile($this->filePath(), '["OptedOut"]');
    }
}
