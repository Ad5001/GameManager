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
       return $this->level;
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
       $this->gm->startGame($this->level);
       return true;
   }


   public function onJoin(Player $player) {
       if($this->main->getInGamePlayers($this->getLevel()) >= $this->getMinPlayers() and !$this->isStarted()) {
           $this->gm->startGame($this->getLevel());
       }
       if($this->main->getInGamePlayers($this->getLevel())<= $this->getMaxPlayers() and !$this->isStarted()) {
           $player->teleport($this->getServer()->getDefaultLevel()->getDefaultSpawn());
           $player->sendMessage("Too many players already in the game !");
       }
   }


   public function onQuit(Player $player) {}


   public function getLogger() {
       return $this->getPlugin()->getLogger();
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


   public function onProjectileLauch(\pocketmine\event\entity\ProjectileLauchEvent $event) {}


   public function onProjectileHit(\pocketmine\event\entity\ProjectileHitEvent $event) {}


   public function getConfig() {
       if(!isset($this->cfg)) {
           $this->cfg = new Config($this->main->getDataFolder() . "games/$this->name/config.yml");
       }
       return $this->cfg;
   }


   public function saveDefaultConfig() {
       @mkdir($this->main->getDataFolder() . "games/" . $this->name);
       file_put_contents($this->main->getDataFolder() . "games/$this->name/config.yml", "");
   }


   public function onCommand(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $cmd, $label, array $args) {}


   abstract public function getMinPlayers() : int;


   abstract public function getMaxPlayers() : int;


   public function getDataFolder() {
       return $this->main->getDataFolder() . "games/$this->name";
   }


   public function registerCommand(string $cmd, string $desc, string $usage, array $aliases = [], string $perm = "gamemanager.command.use") {
       if(!isset($this->main->cmds[$cmd])) {
           $this->main->cmds[$cmd] = new GameCommand($this->main, $cmd, $desc, $usage, $aliases, $this, $perm);
           $this->getServer()->getCommandMap()->register($cmd, $this->main->cmds[$cmd]);
       }
   }


   public function getServer() {
       return $this->getPlugin()->getServer();
   }


   public function getName() : string {
       return explode(get_class($this))[count(explode(get_class($this))) - 1];
   }


   public function broadcastMessage(string $message) {
       foreach($this->getLevel()->getPlayers() as $p) {
           $p->sendMessage($message);
       }
   }


   public function getInGamePlayers() {
       $p = [];
       foreach($this->getLevel()->getPlayers() as $pl) {
           if(!$pl->isSpecator()) {
               $p[] = $pl;
           }
       }
       return $p;
   }


   public function getSpectators() {
       $p = [];
       foreach($this->getLevel()->getPlayers() as $pl) {
           if($pl->isSpecator()) {
               $p[] = $pl;
           }
       }
       return $p;
   }


}