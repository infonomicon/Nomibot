<?php

namespace Nomibot\Plugins\TimeBomb;

class OptOutManager
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Group
     */
    private $optouts;

    /**
     * @param string $filePath The path to the output file
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->load();
    }

    /**
     * Check if a nick is opted out
     *
     * @param  string $nick The nick to check
     * @return boolean
     */
    public function contains($nick)
    {
        return $this->optouts->find($nick) !== null;
    }

    /**
     * Add a nick to the optouts
     *
     * @param string $nick The nick to add
     */
    public function add($nick)
    {
        $this->optouts->add($nick);
        $this->save();
    }

    /**
     * Remove a nick from the optouts
     *
     * @param string $nick The nick to remove
     */
    public function remove($nick)
    {
        $this->optouts->remove($nick);
        $this->save();
    }

    /**
     * Load optouts
     */
    private function load()
    {
        $list = json_decode(file_get_contents($this->filePath), true);
        $this->optouts = new Group($list);
    }

    /**
     * Save optouts
     */
    private function save()
    {
        $data = json_encode($this->optouts->listAll());
        file_put_contents($this->filePath, $data);
    }
}
