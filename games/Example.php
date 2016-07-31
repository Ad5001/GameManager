<?php
use Ad5001\GameManager\Game;
use pocketmine\Player;

class Example extends Game {

    public function onGameStart() {
        $this->getLogger()->info("Game");
    }
}