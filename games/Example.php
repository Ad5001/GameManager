<?php
use Ad5001\GameManager\Game;
use pocketmine\Player;

class Example extends Game {

    public function onGameStart() {
        $this->getLogger()->info("Game started");
    }


    public function onGameStop() {
        $this->getLogger()->info("Game stoped");
    }


    


    public function getName() : string {
        return "Example";
    }


    public function getMinPlayers() : int {
        return 1;
    }


    public function getMaxPlayers() : int {
        return 5;
    }
}