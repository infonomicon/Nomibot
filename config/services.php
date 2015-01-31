<?php

$app['config'] = require 'config.php';

$app->add('irc.connection', 'Phergie\Irc\Connection', true)
    ->withArgument($app['config']['connection']);

$app->singleton('Phergie\Irc\Plugin\React\Pong\Plugin');

$app->singleton('Phergie\Irc\Plugin\React\NickServ\Plugin')
    ->withArgument($app['config']['nickserv']);

$app->singleton('Phergie\Irc\Plugin\React\AutoJoin\Plugin')
    ->withArgument($app['config']['autojoin']);

$app->singleton('Phergie\Irc\Plugin\React\Command\Plugin')
    ->withArgument($app['config']['command']);

$app->singleton('Nomibot\Plugins\ReJoin');
$app->singleton('Nomibot\Plugins\Say');
$app->singleton('Nomibot\Plugins\Ping');
$app->singleton('Nomibot\Plugins\Sheep');
$app->singleton('Nomibot\Plugins\WellDone');
$app->singleton('Nomibot\Plugins\FlipGoat');
$app->singleton('Nomibot\Plugins\Omniscan');
$app->singleton('Nomibot\Plugins\ThisIsNot');
$app->singleton('Nomibot\Plugins\DangerZone');

$app->singleton('Nomibot\Plugins\TimeBomb')
    ->withArgument($app['config']['timebomb']);

$app->singleton('Nomibot\Plugins\Quote')
    ->withArgument($app['config']['quote']);

$app->singleton('Nomibot\Plugins\Joke')
    ->withArgument($app['config']['joke']);

$app->singleton('Nomibot\Plugins\WordGame\Plugin')
    ->withArgument('irc.plugin.wordgame.wordprovider')
    ->withArgument('irc.plugin.wordgame.scoreboard');

$app->add('irc.plugin.wordgame.wordprovider', 'Nomibot\Plugins\WordGame\Providers\JsonWordProvider')
    ->withArgument($app['config']['wordgame']['wordlistfile']);

$app->add('irc.plugin.wordgame.scoreboard', 'Nomibot\Plugins\WordGame\Scoreboards\SqliteScoreboard')
    ->withArgument($app['config']['wordgame']['scorefile']);

