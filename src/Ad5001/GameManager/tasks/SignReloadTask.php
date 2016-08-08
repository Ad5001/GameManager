<?php
namespace Ad5001\GameManager\tasks;
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
                       if(strtolower($t->getText()[0]) == strtolower("[GAME]") and strtolower($class->getLevel()->getName()) == strtolower($t->getText()[1])) {
                           $texts = $t->getText();
                           $texts[0] = str_ireplace("{players}", count($this->main->getInGamePlayers($class->getLevel())), str_ireplace("{max}", $class->getMaxPlayers(), str_ireplace("{game}", $class->getName(), str_ireplace("{level}", $class->getLevel()->getName(), $this->cfg->get("Game1")))));
                           $texts[1] = str_ireplace("{players}", count($this->main->getInGamePlayers($class->getLevel())), str_ireplace("{max}", $class->getMaxPlayers(), str_ireplace("{game}", $class->getName(), str_ireplace("{level}", $class->getLevel()->getName(), $this->cfg->get("Game2")))));
                           $texts[2] = str_ireplace("{players}", count($this->main->getInGamePlayers($class->getLevel())), str_ireplace("{max}", $class->getMaxPlayers(), str_ireplace("{game}", $class->getName(), str_ireplace("{level}", $class->getLevel()->getName(), $this->cfg->get("GameWait3")))));
                           $texts[3] = str_ireplace("{players}", count($this->main->getInGamePlayers($class->getLevel())), str_ireplace("{max}", $class->getMaxPlayers(), str_ireplace("{game}", $class->getName(), str_ireplace("{level}", $class->getLevel()->getName(), $this->cfg->get("GameWait4")))));
                           $t->setText($texts[0], $texts[1], $texts[2], $texts[3]);
                       }
                           $lvlex = explode("{level}", $this->cfg->get("Game2"));
                           $lvl = str_ireplace($lvlex[0], "", $t->getText()[1]); 
                           $lvl = str_ireplace($lvlex[1], "", $lvl);
                           if($name == $lvl) {
                               $lvl = $this->main->getServer()->getLevelByName($lvl);
                               if($this->gameManager->getLevels()[$lvl->getName()]->isStarted()) {
                                   $l3 = str_ireplace("{players}", count($lvl->getPlayers()), $this->cfg->get("InGame3"));
                                   $l3 = str_ireplace("{max}", $class->getMaxPlayers(), $l3);
                                   $l4 = str_ireplace("{players}", count($lvl->getPlayers()), $this->cfg->get("InGame4"));
                                   $l4 = str_ireplace("{max}", $class->getMaxPlayers(), $l4);
                                   $t->setText($t->getText()[0], $t->getText()[1], $l3, $l4);
                               } else {
                                   $l3 = str_ireplace("{players}", count($lvl->getPlayers()), $this->cfg->get("GameWait3"));
                                   $l3 = str_ireplace("{max}", $class->getMaxPlayers(), $l3);
                                   $l4 = str_ireplace("{players}", count($lvl->getPlayers()), $this->cfg->get("GameWait4"));
                                   $l4 = str_ireplace("{max}", $class->getMaxPlayers(), $l4);
                                   $t->setText($t->getText()[0], $t->getText()[1], $l3, $l4);
                               }
                           }
                   }
                   /*if($t->getText()[0] == "FillIt") { // Was just pure curiosity xD
                       $text = [];
                       for($i = 0; $i < 60; $i++) {
                           $text[] = "§4<><>";
                       }
                       $text = implode("", $text);
                       $t->setText($text, $text, $text);
                   }*/
               }
           }
       }
   }
}