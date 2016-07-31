<?php
namespace Ad5001\GameManager;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use Ad5001\GameManager\GameManager;
use Ad5001\GameManager\Main;


class SignReloadTask extends PluginTask {


    protected $manager;


   public function __construct(Main $main) {
       parent::__construct($main);
       $this->main = $main;
       $this->server = $main->getServer();
       $this->cfg = $main->getConfig();
       $this->gameManager = $main->getGameManager();
   }



   public function onRun($tick) {
       foreach($this->server->getLevels() as $level) {
           foreach($level->getTiles() as $t) {
               if($t instanceof \pocketmine\tile\Sign) {
                   foreach($this->gameManager->getLevels() as $name => $class) {
                       if($t->getText()[0] == "[GAME]" and $class->getLevel()->getName() == $t->getText()[1]) {
                           $texts = $t->getText();
                           foreach($texts as $key => $text) {
                               $texts[$key] = str_ireplace("{players}", count($class->getLevel()->getPlayers()), str_ireplace("{max}", $class->getMaxPlayers(), str_ireplace("{game}", $class->getName(), str_ireplace("{level}", $class->getLevel()->getName(), ))))
                           }
                           $t->setText($texts[0], $texts[1], $texts[2], $texts[3]);
                       }
                       if(str_ireplace("{game}", $class->getName(), $this->cfg->get("Game1")) == $t->getText()[0]) {
                           $lvlex = explode("{level}", $this->cfg->get("Game2"));
                           $lvl = str_ireplace($lvlex[0], "", $t->getText()[1]); 
                           $lvl = str_ireplace($lvlex[1], "", $lvl);
                           if($name == $lvl) {
                               if($this->gm->getLevels()[$lvl->getName()]->isStarted()) {
                                   $l3 = str_ireplace("{players}", count($lvl->getPlayers()), $this->cfg->get("InGame3"));
                                   $l3 = str_ireplace("{max}", $class->getMaxPlayers(), $l3);
                                   $l4 = str_ireplace("{players}", count($lvl->getPlayers()), $this->cfg->get("InGame4"));
                                   $l4 = str_ireplace("{max}", $class->getMaxPlayers(), $l4);
                                   $t->setText($t->getText()[0], $t->getText()[1], $l3, $t4);
                               } else {
                                   $l3 = str_ireplace("{players}", count($lvl->getPlayers()), $this->cfg->get("GameWait3"));
                                   $l3 = str_ireplace("{max}", $class->getMaxPlayers(), $l3);
                                   $l4 = str_ireplace("{players}", count($lvl->getPlayers()), $this->cfg->get("GameWait4"));
                                   $l4 = str_ireplace("{max}", $class->getMaxPlayers(), $l4);
                                   $t->setText($t->getText()[0], $t->getText()[1], $l3, $t4);
                               }
                           }
                       }
                   }
               }
           }
       }
   }
}