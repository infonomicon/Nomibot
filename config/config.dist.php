<?php

return [

    'logfile' => __DIR__.'/../var/log/bot.log',

    'connection' => [
        'serverHostname' => '',
        'username' => '',
        'realname' => '',
        'nickname' => '',
    ],

    'nickserv' => [
        'password' => '',
    ],

    'autojoin' => [
        'channels' => [''],
    ],

    'command' => [
        'prefix' => '!',
    ],

    'timebomb' => [
        'optoutfile' => __DIR__.'/../var/timebomb_optouts.json',
    ],

    'quote' => [
        'quotefile' => __DIR__.'/../var/quotes.json',
    ],

    'joke' => [
        'jokefile' => __DIR__.'/../var/jokes.json',
    ],

    'wordgame' => [
        'wordlistfile' => __DIR__.'/../var/wordgame_wordlist.json',
        'scorefile' => __DIR__.'/../var/wordgame_scores.db',
    ],

    'enabled_plugins' => [
        'Phergie\Irc\Plugin\React\Pong\Plugin',
        'Phergie\Irc\Plugin\React\NickServ\Plugin',
        'Phergie\Irc\Plugin\React\AutoJoin\Plugin',
        'Phergie\Irc\Plugin\React\Command\Plugin',
        'Phergie\Irc\Plugin\React\CommandHelp\Plugin',
        'Nomibot\Plugins\ReJoin',
        'Nomibot\Plugins\Say',
        'Nomibot\Plugins\Ping',
        'Nomibot\Plugins\Sheep',
        'Nomibot\Plugins\WellDone',
        'Nomibot\Plugins\FlipGoat',
        'Nomibot\Plugins\Omniscan',
        'Nomibot\Plugins\ThisIsNot',
        'Nomibot\Plugins\DangerZone',
        'Nomibot\Plugins\TimeBomb',
        'Nomibot\Plugins\Quote',
        'Nomibot\Plugins\Joke',
        'Nomibot\Plugins\WordGame\Plugin',
    ],

];
