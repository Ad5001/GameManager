<?php
namespace Ad5001\GameManager;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\Player;
use Ad5001\GameManager\GameManager;
use Ad5001\GameManager\tasks\SignReloadTask;


class Main extends PluginBase implements Listener {


    protected $manager;


   public function onEnable(){
        $this->reloadConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getServer()->getFilePath() . "worldsBackups/");
        @mkdir($this->getDataFolder() . "games");
        $this->manager = new GameManager($this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new SignReloadTask($this), 5);
        foreach(array_diff_key($this->getConfig()->getAll(), ["Game1" => "", "Game2" => "", "InGame3" => "", "InGame4" => "", "GameWait3" => "", "GameWait4" => ""]) as $worldname => $gamename) {
            if($this->getServer()->getLevelByName($worldname) instanceof Level) {
                $this->manager->registerLevel($this->getServer()->getLevelByName($worldname), $gamename);
            }
        }
   }


    public function onLoad(){
        $this->saveDefaultConfig();
    }


    public function getGameManager() {
        return $this->manager;
    }


    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        switch($cmd->getName()){
            case "default":
            break;
        }
     return false;
    }


    public function getClasses(string $file) {
        $tokens = token_get_all($file);
        $class_token = false;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] == T_CLASS) {
                    $class_token = true;
                } else if ($class_token && $token[0] == T_STRING) {
                    return $token[1];
                }
            }
        }
    }


############################
#                                                    #
#           Events for games             #
#                                                    #
############################

public function onInteract(PlayerInteractEvent $event) {
    //    echo $event->getBlock()->getId() . "=/=" . Block::SIGN_POST ."=/=" . Block::WALL_SIGN;
       if($event->getBlock()->getId() == Block::SIGN_POST or $event->getBlock()->getId() == Block::WALL_SIGN) {
           $t = $event->getBlock()->getLevel()->getTile($event->getBlock());
        //    echo "Sign.";
           foreach($this->manager->getLevels() as $class) {
                  if(str_ireplace("{game}", $class->getName(), $this->getConfig()->get("Game1")) == $t->getText()[0]) {
                           $lvlex = explode("{level}", $this->getConfig()->get("Game2"));
                           $lvl = str_ireplace($lvlex[0], "", $t->getText()[1]); 
                           $lvl = str_ireplace($lvlex[1], "", $lvl);
                           if($name == $lvl) {
                               if($this->manager->getLevels()[$lvl->getName()]->isStarted()) {
                                   $event->getPlayer()->teleport($lvl->getDefaultSpawn());
                                   $event->getPlayer()->setGamemode(3);
                               } else {
                                   $event->getPlayer()->teleport($lvl->getDefaultSpawn());
                               }
                           }
                  }
           }
       }
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onInteract($event);
       }
   }


   public function onEntityLevelChange(EntityLevelChangeEvent $event) {
       if(isset($this->manager->getLevels()[$event->getOrigin()->getName()]) and $event->getEntity() instanceof Player) {
           $this->gameManager->getLevels()[$event->getOrigin()->getName()]->onQuit($event->getPlayer());
       }
       if(isset($this->manager->getLevels()[$event->getTarget()->getName()]) and $event->getEntity() instanceof Player) {
           $this->gameManager->getLevels()[$event->getTarget()->getName()]->onJoin($event->getPlayer());
       }
   }


   public function onPlayerChat(\pocketmine\event\player\PlayerChatEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onPlayerChat($event);
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onChat($event);
       }
   }


   public function onPlayerCommandPreprocess(\pocketmine\event\player\PlayerCommandPreprocessEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onPlayerCommand($event);
       }
   }


   public function onPlayerDeath(\pocketmine\event\player\PlayerDeathEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onPlayerDeath($event);
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onDeath($event);
       }
   }


   public function onPlayerDropItem(\pocketmine\event\player\PlayerDropItemEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onPlayerDropItem($event);
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onDrop($event);
       }
   }


   public function onPlayerMove(\pocketmine\event\player\PlayerMoveEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onPlayerMove($event);
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onMove($event);
       }
   }


   public function onPlayerItemConsume(\pocketmine\event\player\PlayerItemConsumeEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onPlayerItemConsume($event);
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onItemConsume($event);
       }
   }


   public function onPlayerItemHeld(\pocketmine\event\player\PlayerItemHeldEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onPlayerItemHeld($event);
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onItemHeld($event);
       }
   }


   public function onBlockBreak(\pocketmine\event\block\BlockBreakEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onBlockBreak($event);
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onBreak($event);
       }
   }


   public function onBlockPlace(\pocketmine\event\block\BlockPlaceEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onBlockPlace($event);
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onPlace($event);
       }
   }


   public function onEntityDamage(\pocketmine\event\entity\EntityDamageEvent $event) {
       if(isset($this->manager->getLevels()[$event->getEntity()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getEntity()->getLevel()->getName()]->onEntityDamage($event);
       }
   }


   public function onDataPacketReceive(\pocketmine\event\server\DataPacketReceiveEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onDataPacketReceive($event);
       }
   }


   public function onDataPacketSend(\pocketmine\event\server\DataPacketSendEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onDataPacketSend($event);
       }
   }


   public function onServerCommand(\pocketmine\event\server\DataPacketReceiveEvent $event) {
       foreach($this->manager->getLevels() as $lvl => $class) {
           $class->onServerCommand($event);
       }
   }

   public function onPlayerJoin(\pocketmine\event\player\PlayerJoinEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onJoin($player);
       }
   }

   public function onPlayerQuit(\pocketmine\event\player\PlayerQuitEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onQuit($player);
       }
   }
}