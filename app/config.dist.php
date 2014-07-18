<?php

/*
|--------------------------------------------------------------------------
| General Settings
|--------------------------------------------------------------------------
*/

$hostname = 'irc.freenode.net';
$username = '';
$realname = '';
$nickname = '';
$password = '';
$channels = [];
$prefix   = '!';
$logfile  = __DIR__ . '/storage/logs/bot.log';

/*
|--------------------------------------------------------------------------
| Plugin Settings
|--------------------------------------------------------------------------
*/

$wordlist = __DIR__ . '/wordlist.txt';
$scores   = __DIR__ . '/storage/wordgame/scores.db';

/*
|--------------------------------------------------------------------------
| Create Phergie Config Array
|--------------------------------------------------------------------------
*/

return [

    'connections' => [
        new Phergie\Irc\Connection([
            'serverHostname' => $hostname,
            'username' => $username,
            'realname' => $realname,
            'nickname' => $nickname,
        ]),
    ],

    'plugins' => [
        new Phergie\Irc\Plugin\React\Pong\Plugin,
        new Phergie\Irc\Plugin\React\NickServ\Plugin([
            'password' => $password,
        ]),
        new Phergie\Irc\Plugin\React\AutoJoin\Plugin([
            'channels' => $channels,
        ]),
        new Phergie\Irc\Plugin\React\Command\Plugin([
            'prefix' => $prefix,
        ]),
        new Infonomicon\IrcBot\ReJoin,
        new Infonomicon\IrcBot\Say,
        new Infonomicon\IrcBot\Sheep,
        new Infonomicon\IrcBot\FlipGoat,
        new Infonomicon\IrcBot\Omniscan,
        new Infonomicon\IrcBot\Minivangi,
        new Infonomicon\IrcBot\DangerZone,
        new Infonomicon\IrcBot\TimeBomb,
        new Infonomicon\IrcBot\WordGame\Plugin([
            'word_provider' => new Infonomicon\IrcBot\WordGame\Providers\FileWordProvider($wordlist),
            'scoreboard' => new Infonomicon\IrcBot\WordGame\Scoreboards\SqliteScoreboard($scores),
        ]),
    ],

    'logfile' => $logfile,

];
