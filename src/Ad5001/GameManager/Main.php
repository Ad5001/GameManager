<?php
namespace Ad5001\GameManager;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;
use Ad5001\GameManager\GameManager;


class Main extends PluginBase{


   public function onEnable(){
        $this->reloadConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->manager = new GameManager($this);

   }


    public function onLoad(){
        $this->saveDefaultConfig();
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