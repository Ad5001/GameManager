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
    protected $server;


   public function __construct(string $name, Level $level) {
       $this->server = $level->getServer();
       $this->level = $level;
       $this->name = $name;
       $this->main = $this->server->getPlugin("GameManager");
       $this->gm = $this->main->getGameManager();
       $this->main->backup($level);
   }


   public function getPlugin() {
       return $this->main;
   }


   public function getLevel() {
       return $this->main;
   }


   public function isStarted() {
       return isset($this->gm->getStartedGames()[$this->level->getName()]);
   }


   public function onGameStart();


   public function onGameStop();


   public function stopGame() {
       $this->main->getGameManager()->reloadLevel($this->level);
       return true;
   }


   public function onJoin(Player $player) {}


   public function onQuit(Player $player) {}


   public function onInteract(\pocketmine\event\player\PlayerInteract $event) {}


   public function onBlockBreak(\pocketmine\event\block\BlockBreakEvent $event) {}


   public function onBlockPlace(\pocketmine\event\entity\EntityDamageEvent $event) {}


   public function onEntityDamage(\pocketmine\event\entity\EntityDamageEvent $event) {}


   public function getConfig() {
       return new Config($this->main->getDataFolder() . "games/$this->name");
   }


   public function saveDefaultConfig() {
       file_put_contents($this->main->getDataFolder() . "games/$this->name", "");
   }



   public function getName() : string;


   public function getMinPlayers() : int;


   public function getMaxPlayers() : int;


   public function useEvent(\pocketmine\event\Event $event) : bool;


   public function getDataFolder() {
       return $this->main->getDataFolder() . "games/$this->name";
   }


}