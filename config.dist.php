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
$logfile  = __DIR__.'/storage/logs/bot.log';

/*
|--------------------------------------------------------------------------
| Plugin Settings
|--------------------------------------------------------------------------
*/

$wordlist = __DIR__.'/storage/wordgame/wordlist.json';
$scores   = __DIR__.'/storage/wordgame/scores.db';
$quotes   = __DIR__.'/storage/quote/quotes.json';
$bombouts = __DIR__.'/storage/timebomb/optouts.json';

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
        new Infonomicon\IrcBot\Ping,
        new Infonomicon\IrcBot\Sheep,
        new Infonomicon\IrcBot\FlipGoat,
        new Infonomicon\IrcBot\Omniscan,
        new Infonomicon\IrcBot\DangerZone,
        new Infonomicon\IrcBot\TimeBomb($bombouts),
        new Infonomicon\IrcBot\Quote($quotes),
        new Infonomicon\IrcBot\WordGame\Plugin([
            'word_provider' => new Infonomicon\IrcBot\WordGame\Providers\JsonWordProvider($wordlist),
            'scoreboard' => new Infonomicon\IrcBot\WordGame\Scoreboards\SqliteScoreboard($scores),
        ]),
    ],

    'logfile' => $logfile,

];