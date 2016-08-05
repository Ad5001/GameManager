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
        foreach(array_diff_key($this->getConfig()->getAll(), ["Game1" => $this->getConfig()->get("Game1"), "Game2" => $this->getConfig()->get("Game2"), "InGame3" => $this->getConfig()->get("InGame3"), "InGame4" => $this->getConfig()->get("InGame4"), "GameWait3" => $this->getConfig()->get("InGame4"), "GameWait4" => $this->getConfig()->get("InGame4")]) as $worldname => $gamename) {
            $lvl = $this->getLevelByName($worldname);
            // $this->getLogger()->info(get_class($lvl));
            if($lvl instanceof Level) {
                // $this->getLogger()->info("Registering $worldname");
                $this->manager->registerLevel($lvl, $gamename);
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
            case "games":
            if(!isset($args[0])) {
                $games = [];
                foreach($this->manager->getGames() as $g) {
                    array_push($games, explode("\\", $g)[count(explode("\\", $g)) - 1]);
                }
                $sender->sendMessage("§l§o[§r§lGameManager§o]§r Current existings games: " . implode(", ", $games) . ". \nUse /games <game_name> to get all the levels of a game.");
            } else {
                $sender->sendMessage("§l§o[§r§lGameManager§o]§r Current levels running $args[0]:");
                foreach($this->manager->getLevels() as $levelname => $game) {
                    if(strtolower($game->getName()) == strtolower($args[0])) {
                        $p = $this->getInGamePlayers($game->getLevel());
                        $s = $this->getSpectators($game->getLevel());
                        $sender->sendMessage("§l§o[§r§lGameManager§o]§r§a " . $levelname . "    Is started: " . $game->isStarted() . "    Players: " . $p . "    Spectators: " . $s);
                    }
                }
            }
            return true;
            break;
        }
        if(isset($this->cmds[$cmd->getName()])) {
            $this->cmds[$cmd->getName()]->onCommand($sender, $cmd, $label, $args);
        }
     return false;
    }

    public function getInGamePlayers(Level $level) {
        $p = 0;
        foreach($level->getPlayers() as $pl) {
                            if($this->getServer()->getPluginManager()->getPlugin("SpectatorPlus") !== null) {
                                if(!$this->getServer()->getPluginManager()->getPlugin("SpectatorPlus")->isSpectator($pl)) {
                                    array_push($p, $pl); 
                                }
                            } else {
                                if(!$pl->isSpectator()) {
                                    array_push($p, $pl); 
                                }
                            }
        }
        return count($p);
    }


    public function getSpectators(Level $level) {
        $s = 0;
        foreach($level->getPlayers() as $pl) {
                            if($this->getServer()->getPluginManager()->getPlugin("SpectatorPlus") !== null) {
                                if($this->getServer()->getPluginManager()->getPlugin("SpectatorPlus")->isSpectator($pl)) {
                                    array_push($s, $pl);
                                }
                            } else {
                                if($pl->isSpectator()) {
                                    array_push($s, $pl);
                                }
                            }
        }
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


    public function getLevelByName(string $name) {
        foreach($this->getServer()->getLevels() as $level) {
            if($level->getName() == $name) {
                return $level;
            }
        }
        return null;
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
                           if($class->getLevel()->getName() == $lvl) {
                               if($this->manager->getLevels()[$lvl]->isStarted()) {
                                   $event->getPlayer()->teleport($class->getLevel()->getSafeSpawn());
                                   $event->getPlayer()->setGamemode(3);
                               } else {
                                   $event->getPlayer()->teleport($class->getLevel()->getSafeSpawn());
                               }
                           }
                  }
           }
       }
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onInteract($event);
       }
   }



   public function onLevelLoad(\pocketmine\event\level\LevelLoadEvent $event) {
       if($this->getConfig()->get($event->getLevel()->getName()) !== null) {
           $this->manager->registerLevel($event->getLevel(), $this->getConfig()->get($event->getLevel()->getName()));
       }
   }


   public function onEntityLevelChange(EntityLevelChangeEvent $event) {
       if(isset($this->manager->getLevels()[$event->getOrigin()->getName()]) and $event->getEntity() instanceof Player) {
           $this->manager->getLevels()[$event->getOrigin()->getName()]->onQuit($event->getEntity());
       }
       if(isset($this->manager->getLevels()[$event->getTarget()->getName()]) and $event->getEntity() instanceof Player) {
           $this->manager->getLevels()[$event->getTarget()->getName()]->onJoin($event->getEntity());
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


   public function onServerCommand(\pocketmine\event\server\ServerCommandEvent $event) {
       foreach($this->manager->getLevels() as $lvl => $class) {
           $class->onServerCommand($event);
       }
   }

   public function onPlayerJoin(\pocketmine\event\player\PlayerJoinEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onJoin($event->getPlayer());
       }
   }

   public function onPlayerQuit(\pocketmine\event\player\PlayerQuitEvent $event) {
       if(isset($this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()])) {
           $this->manager->getLevels()[$event->getPlayer()->getLevel()->getName()]->onQuit($event->getPlayer());
       }
   }
}