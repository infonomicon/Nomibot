<?php

namespace Nomibot\Plugins\WordGame\Scoreboards;

use Nomibot\Plugins\WordGame\Stats;
use Nomibot\Plugins\WordGame\Scoreboard;

class SqliteScoreboardTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $db = new \PDO('sqlite:' . __DIR__ . '/stubs/test.db');
        $db->query('CREATE TABLE scores (nick TEXT, scored_at DATETIME)');
        $db->query("INSERT INTO scores (nick, scored_at) VALUES ('tester2', '2014-01-30 10:12:35')");
        $db->query("INSERT INTO scores (nick, scored_at) VALUES ('tester2', '2014-01-30 10:13:35')");
        $db->query("INSERT INTO scores (nick, scored_at) VALUES ('tester2', '2014-01-30 10:14:35')");
        $db->query("INSERT INTO scores (nick, scored_at) VALUES ('tester3', '2014-01-30 10:15:35')");
        $db->query("INSERT INTO scores (nick, scored_at) VALUES ('tester3', datetime('now'))");
        $db->query("INSERT INTO scores (nick, scored_at) VALUES ('tester', '2014-01-30 10:08:35')");
        $db->query("INSERT INTO scores (nick, scored_at) VALUES ('tester', '2014-01-30 10:09:35')");
        $db->query("INSERT INTO scores (nick, scored_at) VALUES ('tester', '2014-01-30 10:10:35')");
        $db->query("INSERT INTO scores (nick, scored_at) VALUES ('tester', '2014-01-30 10:11:35')");
    }

    public function tearDown()
    {
        $db = new \PDO('sqlite:' . __DIR__ . '/stubs/test.db');
        $db->query('DROP TABLE scores');
    }

    public function testConstructorWithDefault()
    {
        $sb = new SqliteScoreboard;
        $this->assertTrue($sb instanceof Scoreboard);
    }

    public function testConstructorWithFilename()
    {
        $sb = new SqliteScoreboard(__DIR__ . '/stubs/test.db');
        $this->assertTrue($sb instanceof Scoreboard);
    }

    public function testConstructorFails_InvalidFilePath()
    {
        $this->setExpectedException('RuntimeException');
        new SqliteScoreboard(__DIR__ . '/stubs/nodir/test.db');
    }

    public function testGetStats()
    {
        $sb = new SqliteScoreboard(__DIR__ . '/stubs/test.db');

        $stats = $sb->getStats('tester');
        $this->assertTrue($stats instanceof Stats);
        $this->assertEquals(4, $stats->getTotal());
        $this->assertEquals(0, $stats->getToday());

        $stats = $sb->getStats('tester2');
        $this->assertEquals(3, $stats->getTotal());
        $this->assertEquals(0, $stats->getToday());

        $stats = $sb->getStats('tester3');
        $this->assertEquals(2, $stats->getTotal());
        $this->assertEquals(1, $stats->getToday());
    }

    public function addWin()
    {
        $sb = new SqliteScoreboard(__DIR__ . '/stubs/test.db');

        $sb->addWin('tester');
        $stats = $sb->getStats('tester');
        $this->assertEquals(5, $stats->getTotal());
        $this->assertEquals(1, $stats->getToday());
    }

    public function testGetTopTen()
    {
        $sb = new SqliteScoreboard(__DIR__ . '/stubs/test.db');

        $topStats = $sb->getTopTen();

        $this->assertEquals(3, count($topStats));
        $this->assertEquals('tester', $topStats[0]->getNick());
        $this->assertEquals('tester2', $topStats[1]->getNick());
        $this->assertEquals('tester3', $topStats[2]->getNick());
    }
}
