<?php
use Ad5001\GameManager\Game;
use pocketmine\Player;

class Example extends Game {

    public function onGameStart() {
        $this->getLogger()->info("Game started");
    }


    public function onGameStop() {
        $this->getLogger()->info("Game stoped");
        foreach($this->getLevel()->getPlayers() as $p) {
            $p->setGamemode(0);
            $p->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
        }
    }


    public function onJoin(Player $player) {
        parent::onJoin($player);
        $this->getPlugin()->getLogger()->info($player->getName() . " joined the game " . $this->getName() . " in world " . $this->getLevel()->getName());
    }


    public function onQuit(Player $player) {
        parent::onJoin($player);
        $this->getPlugin()->getLogger()->info($player->getName() . " left the game " . $this->getName() . " in world " . $this->getLevel()->getName());
    }


    public function onPlayerDeath(\pocketmine\event\PlayerDeathEvent $event) {
        if(($this->getPlugin()->getInGamePlayers($this->getLevel()) - 1) == 0) {
            $this->stop();
        }
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