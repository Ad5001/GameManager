<?php
namespace Ad5001\GameManager;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\utils\Config;

use Ad5001\GameManager\Main;



abstract class Game {


    protected $name;
    protected $level;


   public function __construct(string $name, Level $level) {
       $this->server = $level->getServer();
       $this->level = $level;
       $this->name = $name;
       $this->main = $this->server->getPlugin("GameManager");
   }


   public function getPlugin() {
       return $this->main;
   }


   public function getLevel() {
       return $this->main;
   }


   public function getLevel() {
       return $this->main;
   }


   public function onGameStart();


   public function stopGame();


   public function onJoin(Player $player) {}


   public function onQuit(Player $player) {}


   public function onBlockBreak(Player $player, Block $block) {}


   public function onBlockPlace(Player $player, Block $block) {}


   public function getConfig() {
       return new Config($this->main->getDataFolder() . "games/$this->name");
   }


   public function saveDefaultConfig() {
       $this->main->saveResource("/games/$this->name/config.yml");
   }



   public function getName() : string;


   public function getMinPlayers() : int;


   public function getMaxPlayers() : int;


   public function useEvent(\pocketmine\event\Event $event) : bool;



}