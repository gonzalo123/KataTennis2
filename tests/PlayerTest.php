<?php

use G\Mediator;

class PlayerTest extends \PHPUnit_Framework_TestCase
{
    public function testPlayerScores()
    {
        $playerScore = [
            'Gonzalo' => false
        ];

        $mediator = new Mediator();
        $mediator->connect('player.scores', function($playerName) use (&$playerScore) {
                $playerScore[$playerName] = true;
        });
        $player = new Player('Gonzalo', $mediator);

        $this->assertEquals('Gonzalo', $player->getName());
        $this->assertFalse($playerScore['Gonzalo']);
        $player->scores();
        $this->assertTrue($playerScore['Gonzalo']);
    }

    public function testTwoPlayersScore()
    {
        $playerScore = [
            'Gonzalo' => 0,
            'Peter'   => 0
        ];

        $mediator = new Mediator();
        $mediator->connect('player.scores', function($playerName) use (&$playerScore) {
                $playerScore[$playerName]++;
            });

        $player1 = new Player('Gonzalo', $mediator);
        $player2 = new Player('Peter', $mediator);

        $this->assertEquals(0, $playerScore['Gonzalo']);
        $this->assertEquals(0, $playerScore['Peter']);

        $player1->scores();
        $this->assertEquals(1, $playerScore['Gonzalo']);
        $this->assertEquals(0, $playerScore['Peter']);

        $player2->scores();
        $this->assertEquals(1, $playerScore['Gonzalo']);
        $this->assertEquals(1, $playerScore['Peter']);
    }
}