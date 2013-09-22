<?php

use G\Mediator;

class ScoreBoard
{
    private $mediator;
    private $player1;
    private $player2;
    private $score = [];
    private $advantage;
    private $scoreSecuence = [0, 15, 30, 40];
    private $deuce = false;

    function __construct(Mediator $mediator)
    {
        $this->mediator = $mediator;
        $this->mediator->connect('player.scores', function ($playerName) {
                $this->playerScores($playerName);
            });
    }

    protected function playerScores($playerName)
    {
        if (!$this->deuce) {
            $this->score[$playerName]++;
        }

        $this->deuce ? $this->performDeuce($playerName) : $this->performNoDeuce($playerName);
    }

    public function registerPlayer1(Player $player)
    {
        $this->player1 = $player;
    }

    public function registerPlayer2(Player $player)
    {
        $this->player2 = $player;
    }

    public function initGame()
    {
        $this->score[$this->getPlayer1Name()] = 0;
        $this->score[$this->getPlayer2Name()] = 0;

        $this->advantage = null;
    }

    public function getScore()
    {
        return [
            $this->getPlayer1Name() => $this->getPlayer1Score(),
            $this->getPlayer2Name() => $this->getPlayer2Score()
        ];
    }

    public function isDeuce()
    {
        return $this->deuce;
    }

    public function getAdvantage()
    {
        return $this->advantage;
    }

    protected function performDeuce($playerName)
    {
        if (is_null($this->advantage)) {
            $this->advantage = $playerName;
        } else {
            if ($this->advantage == $playerName) {
                $this->mediator->trigger('player.wins', ['playerName' => $playerName]);
            } else {
                $this->advantage = null;
            }
        }
    }

    protected function performNoDeuce($playerName)
    {
        $player1 = $this->getPlayer1Name();
        $player2 = $this->getPlayer2Name();

        if ($this->score[$player1] == $this->score[$player2] && $this->score[$player2] == 3) {
            $this->deuce = true;
        } else {
            if ($this->score[$playerName] > 3) {
                $this->mediator->trigger('player.wins', ['playerName' => $playerName]);
            }
        }
    }

    protected function getPlayer1Score()
    {
        return $this->scoreSecuence[$this->score[$this->getPlayer1Name()]];
    }

    protected function getPlayer2Score()
    {
        return $this->scoreSecuence[$this->score[$this->getPlayer2Name()]];
    }

    protected function getPlayer1Name()
    {
        return $this->player1->getName();
    }

    protected function getPlayer2Name()
    {
        return $this->player2->getName();
    }
}