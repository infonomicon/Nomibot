<?php

namespace Infonomicon\IrcBot\WordGame\Scoreboards;

use PDO;
use Infonomicon\IrcBot\WordGame\Scoreboard;
use Infonomicon\IrcBot\WordGame\Stats;

/**
 * Sqlite Scoreboard
 *
 * Not very efficient, but it's a damn word game.
 * Feel free to tweak it if you're bored.
 */
class SqliteScoreboard implements Scoreboard
{
    /**
     * @var \PDO
     */
    private $db;

    /**
     * @param string $filename
     */
    public function __construct($filename = null)
    {
        if (!$filename) {
            $filename = __DIR__ . '/default/scores.db';
        }

        try {
            $this->db = new PDO("sqlite:{$filename}");
        } catch (\Exception $e) {
            throw new \RuntimeException('Cannot open sqlite db');
        }
    }

    /**
     * Track a win
     *
     * @param string $nick
     */
    public function addWin($nick)
    {
        $sth = $this->db->prepare("INSERT INTO scores (nick, scored_at) VALUES (?, datetime('now'))");
        $sth->execute([$nick]);
    }

    /**
     * Get the score for a nick
     *
     * @param string $nick
     * @return Stats
     */
    public function getStats($nick)
    {
        $sth = $this->db->prepare('SELECT COUNT(*) FROM scores WHERE nick = ?');
        $sth->execute([$nick]);
        $total = $sth->fetchColumn();

        $sth = $this->db->prepare("SELECT COUNT(*) FROM scores WHERE nick = ? AND scored_at >= DATE('now')");
        $sth->execute([$nick]);
        $today = $sth->fetchColumn();

        return new Stats($nick, $total, $today);
    }

    /**
     * Get the top ten scores
     *
     * @return Stats[]
     */
    public function getTopTen()
    {
        $scores = [];

        $sth = $this->db->prepare('SELECT nick FROM scores GROUP BY nick ORDER BY COUNT(*) DESC LIMIT 10');
        $sth->execute();

        while ($nick = $sth->fetchColumn()) {
            $scores[] = $this->getStats($nick);
        }

        return $scores;
    }
}
