<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

class HelpAction extends BaseAction
{
    /**
     * @var array
     */
    private $helpText = [
        'timebomb' => [
            'timebomb [nick:optional]',
            '========================',
            'Start a timebomb game. It starts with you, unless another nick is specified.',
        ],

        'bombtoss' => [
            'bombtoss [nick]',
            '===============',
            'When a timebomb game is running, and you have the bomb, this will pass it to another player.',
        ],

        'cut' => [
            'cut [color]',
            '===========',
            'When a timebomb game is running, and you have the bomb, this will let you cut a wire in an attempt to defuse it.',
        ],

        'bombout' => [
            'bombout (no arguments)',
            '======================',
            'Opt-out of the timebomb game.',
        ],

        'bombin' => [
            'bombin (no arguments)',
            '=====================',
            'Opt-in to the timebomb game.',
        ],
    ];

    /**
     * Show help text
     */
    public function __invoke()
    {
        $command = $this->event->getCustomCommand();

        if ($command === 'help') {
            $params = $this->event->getCustomParams();
            $section = reset($params);
        } else {
            $section = str_replace('.help', '', $command);
        }

        if (!isset($this->helpText[$section])) {
            return;
        }

        foreach ($this->helpText[$section] as $line) {
            $this->message($line);
        }
    }
}
