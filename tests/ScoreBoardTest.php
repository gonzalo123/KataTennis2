<?php

use G\Mediator;

class ScoreBoardTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleGame()
    {
        $mediator = new Dispatcher();

        $scoreBoard = new ScoreBoard($mediator);
        $player1 = $this->getPlayer1Mock($mediator, 'Gonzalo');
        $player2 = $this->getPlayer1Mock($mediator, 'Peter');

        $scoreBoard->registerPlayer1($player1);
        $scoreBoard->registerPlayer2($player2);

        $scoreBoard->initGame();

        $this->assertEquals(['Gonzalo' => 0, 'Peter' => 0], $scoreBoard->getScore());

        $player1->scores();
        $this->assertEquals(['Gonzalo' => 15, 'Peter' => 0], $scoreBoard->getScore());

        $player2->scores();
        $this->assertEquals(['Gonzalo' => 15, 'Peter' => 15], $scoreBoard->getScore());
    }

    public function testPlayer1Wins()
    {
        $mediator = new Dispatcher();

        $scoreBoard = new ScoreBoard($mediator);
        $player1 = $this->getPlayer1Mock($mediator, 'Gonzalo');
        $player2 = $this->getPlayer1Mock($mediator, 'Peter');

        $scoreBoard->registerPlayer1($player1);
        $scoreBoard->registerPlayer2($player2);

        $player1Wins = false;
        $mediator->connect(Dispatcher::PLAYER_WINS, function($playerName) use (&$player1Wins){
                $this->assertEquals('Gonzalo', $playerName);
                $player1Wins = true;
            });

        $scoreBoard->initGame();

        $this->assertFalse($player1Wins);
        $this->assertEquals(['Gonzalo' => 0, 'Peter' => 0], $scoreBoard->getScore());
        $player1->scores();
        $this->assertEquals(['Gonzalo' => 15, 'Peter' => 0], $scoreBoard->getScore());
        $player1->scores();
        $this->assertEquals(['Gonzalo' => 30, 'Peter' => 0], $scoreBoard->getScore());
        $player1->scores();
        $this->assertEquals(['Gonzalo' => 40, 'Peter' => 0], $scoreBoard->getScore());

        $this->assertFalse($player1Wins);
        $player1->scores();

        $this->assertTrue($player1Wins);
    }

    public function testDeuceGame()
    {
        $mediator = new Dispatcher();

        $scoreBoard = new ScoreBoard($mediator);
        $player1 = $this->getPlayer1Mock($mediator, 'Gonzalo');
        $player2 = $this->getPlayer1Mock($mediator, 'Peter');

        $player1Wins = false;
        $mediator->connect(Dispatcher::PLAYER_WINS, function($playerName) use (&$player1Wins){
                $this->assertEquals('Gonzalo', $playerName);
                $player1Wins = true;
            });

        $scoreBoard->registerPlayer1($player1);
        $scoreBoard->registerPlayer2($player2);

        $scoreBoard->initGame();

        $this->assertEquals(['Gonzalo' => 0, 'Peter' => 0], $scoreBoard->getScore());

        $player1->scores();
        $this->assertEquals(['Gonzalo' => 15, 'Peter' => 0], $scoreBoard->getScore());
        $player1->scores();
        $this->assertEquals(['Gonzalo' => 30, 'Peter' => 0], $scoreBoard->getScore());
        $player1->scores();
        $this->assertEquals(['Gonzalo' => 40, 'Peter' => 0], $scoreBoard->getScore());

        $this->assertFalse($scoreBoard->isDeuce());
        $player2->scores();
        $this->assertEquals(['Gonzalo' => 40, 'Peter' => 15], $scoreBoard->getScore());
        $player2->scores();
        $this->assertEquals(['Gonzalo' => 40, 'Peter' => 30], $scoreBoard->getScore());
        $this->assertFalse($scoreBoard->isDeuce());
        $this->assertNull($scoreBoard->getAdvantage());

        $player2->scores();
        $this->assertEquals(['Gonzalo' => 40, 'Peter' => 40], $scoreBoard->getScore());
        $this->assertTrue($scoreBoard->isDeuce());
        $this->assertNull($scoreBoard->getAdvantage());

        $player1->scores();
        $this->assertFalse($player1Wins);
        $player1->scores();
        $this->assertTrue($player1Wins);
    }

    public function testDeuceGameMoreComplicated()
    {
        $mediator = new Dispatcher();

        $scoreBoard = new ScoreBoard($mediator);
        $player1 = $this->getPlayer1Mock($mediator, 'Gonzalo');
        $player2 = $this->getPlayer1Mock($mediator, 'Peter');

        $player2Wins = false;
        $mediator->connect(Dispatcher::PLAYER_WINS, function($playerName) use (&$player2Wins){
                $this->assertEquals('Peter', $playerName);
                $player2Wins = true;
            });

        $scoreBoard->registerPlayer1($player1);
        $scoreBoard->registerPlayer2($player2);

        $scoreBoard->initGame();
        $this->assertEquals(['Gonzalo' => 0, 'Peter' => 0], $scoreBoard->getScore());

        $player1->scores();
        $player1->scores();
        $player1->scores();

        $player2->scores();
        $player2->scores();
        $this->assertFalse($scoreBoard->isDeuce());
        $player2->scores();

        $this->assertEquals(['Gonzalo' => 40, 'Peter' => 40], $scoreBoard->getScore());
        $this->assertTrue($scoreBoard->isDeuce());
        $this->assertNull($scoreBoard->getAdvantage());
        $player1->scores();
        $this->assertEquals('Gonzalo', $scoreBoard->getAdvantage());
        $player2->scores();
        $this->assertNull($scoreBoard->getAdvantage());
        $player2->scores();
        $this->assertEquals('Peter', $scoreBoard->getAdvantage());
        $player2->scores();
        $this->assertTrue($player2Wins);

    }

    private function getPlayer1Mock($mediator, $playerName)
    {
        $player = $this->getMockBuilder('Player')->disableOriginalConstructor()->getMock();
        $player->expects($this->any())->method('scores')->will(
            $this->returnCallback(
                function () use ($mediator, $playerName) {
                    $mediator->trigger(Dispatcher::PLAYER_SCORES, ['playerName' => $playerName]);
                }
            )
        );
        $player->expects($this->any())->method('getName')->will($this->returnValue($playerName));

        return $player;
    }
}