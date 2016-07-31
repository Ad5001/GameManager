<?php
namespace Ad5001\GameManager;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\Player;
use Ad5001\GameManager\GameManager;


class Main extends PluginBase implements Listener {


    protected $manager;


   public function onEnable(){
        $this->reloadConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getServer()->getFilePath() . "worldsBackups/");
        @mkdir($this->getDataFolder() . "games");
        $this->manager = new GameManager($this);
        foreach(array_diff_key($this->getConfig()->getAll(), ["Game1" => "", "Game2" => "", "InGame3" => "", "InGame4" => "", "GameWait3" => "", "GameWait4" => ""]) as $worldname => $gamename) {
            if($this->getServer()->getLevelByName($worldname) instanceof Level) {
                $this->manager->registerLevel($this->getServer()->getLevelByName($worldname), $gamename);
            }
        }
   }


   public function onInteract(PlayerInteractEvent $event) {
       if($event->getBlock() instanceof \pocketmine\block\SignPost and $event->getBlock() instanceof \pocketmine\block\WallSign) {
           $t = $event->getBlock()->getLevel()->getTile($block);
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
                                   $this->gameManager->getLevels()[$lvl->getName()]->onJoin($event->getPlayer());
                               }
                           }
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
        $tokens = token_get_all($php_file);
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
}