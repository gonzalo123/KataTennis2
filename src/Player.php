<?php

use G\Mediator;

class Player
{
    private $mediator;
    private $name;

    public function __construct($name, Dispatcher $mediator)
    {
        $this->mediator = $mediator;
        $this->name     = $name;
    }

    public function scores()
    {
        $this->mediator->trigger(Dispatcher::PLAYER_SCORES, ['playerName' => $this->name]);
    }

    public function getName()
    {
        return $this->name;
    }
}