<?php
namespace Ad5001\GameManager;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\utils\Config;
use pocketmine\level\Level;

use Ad5001\GameManager\Main;



abstract class Game {


    protected $name;
    protected $level;
    protected $server;


   public function __construct(string $name, Level $level) {
       $this->server = $level->getServer();
       $this->level = $level;
       $this->name = $name;
       $this->main = $this->server->getPluginManager()->getPlugin("GameManager");
       $this->gm = $this->main->getGameManager();
       $this->gm->backup($level);
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


   abstract public function onGameStart();


   abstract public function onGameStop();


   public function stop() {
       $this->gm->stopGame($this->level);
       return true;
   }


   public function start() {
       $this->gm->stopGame($this->level);
       return true;
   }


   public function onJoin(Player $player) {
       if($this->getLevel()->getPlayers() >= $this->getMinPlayers() and !$this->isStarted()) {
           $this->gm->startGame($this->getLevel());
       }
       if($this->getLevel()->getPlayers() <= $this->getMaxPlayers() and !$this->isStarted()) {
           $player->teleport($this->getServer()->getDefaultLevel()->getDefaultSpawn());
       }
   }


   public function onQuit(Player $player) {
       if($this->getLevel()->getPlayers() <= $this->getMinPlayers()) {
           $this->gm->stopGame($this->getLevel());
       }
   }


   public function onInteract(\pocketmine\event\player\PlayerInteractEvent $event) {}


   public function onChat(\pocketmine\event\player\PlayerChatEvent $event) {}


   public function onPlayerChat(\pocketmine\event\player\PlayerChatEvent $event) {}


   public function onPlayerCommand(\pocketmine\event\player\PlayerCommandPreprocessEvent $event) {}


   public function onDeath(\pocketmine\event\player\PlayerDeathEvent $event) {}


   public function onPlayerDeath(\pocketmine\event\player\PlayerDeathEvent $event) {}


   public function onPlayerDropItem(\pocketmine\event\player\PlayerDropItemEvent $event) {}


   public function onDrop(\pocketmine\event\player\PlayerDropItemEvent $event) {}


   public function onPlayerMove(\pocketmine\event\player\PlayerMoveEvent $event) {}


   public function onMove(\pocketmine\event\player\PlayerMoveEvent $event) {}


   public function onPlayerItemConsume(\pocketmine\event\player\PlayerItemConsumeEvent $event) {}


   public function onItemConsume(\pocketmine\event\player\PlayerItemConsumeEvent $event) {}


   public function onPlayerItemHeld(\pocketmine\event\player\PlayerItemHeldEvent $event) {}


   public function onItemHeld(\pocketmine\event\player\PlayerItemHeldEvent $event) {}


   public function onDataPacketReceive(\pocketmine\event\server\DataPacketReceiveEvent $event) {}


   public function onDataPacketSend(\pocketmine\event\server\DataPacketSendEvent $event) {}


   public function onServerCommand(\pocketmine\event\server\ServerCommandEvent $event) {}


   public function onBlockBreak(\pocketmine\event\block\BlockBreakEvent $event) {}


   public function onBreak(\pocketmine\event\block\BlockBreakEvent $event) {}


   public function onBlockPlace(\pocketmine\event\block\BlockPlaceEvent $event) {}


   public function onPlace(\pocketmine\event\block\BlockPlaceEvent $event) {}


   public function onEntityDamage(\pocketmine\event\entity\EntityDamageEvent $event) {}


   public function getConfig() {
       return new Config($this->main->getDataFolder() . "games/$this->name");
   }


   public function saveDefaultConfig() {
       @mkdir($this->main->getDataFolder() . "games/" . $this->name);
       file_put_contents($this->main->getDataFolder() . "games/$this->name", "");
   }



   abstract public function getName() : string;


   abstract public function getMinPlayers() : int;


   abstract public function getMaxPlayers() : int;


   public function getDataFolder() {
       return $this->main->getDataFolder() . "games/$this->name";
   }


}