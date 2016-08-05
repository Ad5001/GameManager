<?php
namespace Ad5001\GameManager;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Level;



class GameManager {


    protected $main;
    protected $server;
    protected $games;
    protected $levels;
    protected $startedgames;


   public function __construct(Main $main) {
        $this->main = $main;
        $this->server = $main->getServer();
        $files = array_diff(scandir($main->getDataFolder() . "games"), [".", ".."]);
        $this->games = [];
        $this->levels = [];
        $this->startedgames = [];
        foreach ($files as $file) {
            if(!is_dir($this->main->getDataFolder() . "games/" . $file)) {
                require($this->main->getDataFolder() . "games/" . $file);
                $classn = $this->main->getClasses(file_get_contents($this->main->getDataFolder() . "games/" . $file));
                $this->games[explode("\\", $classn)[count(explode("\\", $classn)) - 1]] = $classn;
                @mkdir($this->main->getDataFolder() . "games/" . explode("\\", $classn)[count(explode("\\", $classn)) - 1]);
            }
        }
    }



    public function startGame(Level $level) {
        if(isset($this->levels[$level->getName()]) and !isset($this->startedgames[$level->getName()])) {
            $this->startedgames[$level->getName()] = true;
            $this->levels[$level->getName()]->onGameStart();
            return true;
        }
        return false;
    }



    public function stopGame(Level $level) {
        if(isset($this->startedgames[$level->getName()])) {
            unset($this->startedgames[$level->getName()]);
            $this->levels[$level->getName()]->onGameStop();
            return true;
        }
        return false;
    }



    public function registerLevel(Level $level, string $game) {
        if(!array_key_exists($level->getName(), $this->levels)) {
            if(isset($this->games[$game])) {
                $this->levels[$level->getName()] = new $this->games[$game]($game, $level);
            } else {
                $this->main->getLogger()->warn("No game found with name $game");
            }
        } else {
            $this->main->getLogger()->warn("{$level->getName()} is already registered.");
        }
    }


    public function getLevels() {
        return $this->levels;
    }


    public function getGames() {
        return $this->games;
    }


    public function getStartedGames() {
        return $this->startedgames;
    }



    public function restoreBackup(Level $level) {
        $this->rrmdir($this->server->getFilePath() . "worlds/" . $level->getFolderName());
        @mkdir($this->server->getFilePath() . "worlds/{$level->getFolderName()}");
        $this->copydir($this->server->getFilePath() . "worldsBackups/{$level->getName()}", $this->server->getFilePath() . "worlds/" . $level->getFolderName());
    }



   public function backup(Level $level) {
        $this->rrmdir($this->server->getFilePath() . "worldsBackups/{$level->getName()}");
        @mkdir($this->server->getFilePath() . "worldsBackup/{$level->getName()}");
        $this->copydir($this->server->getFilePath() . "worlds/" . $level->getFolderName(), $this->server->getFilePath() . "worldsBackup/{$level->getName()}");
   } 


    private function rrmdir($dir) { // This is from PHP.NET
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
    
    
    private function copydir($source, $target) {
    if (is_dir($source)) {
        @mkdir($target);        
        @mkdir($target . "region");
        $d = dir($source);
        while ( FALSE !== ( $entry = $d->read() ) ) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $Entry = $source . '/' . $entry; 
            if (is_dir($Entry)) {
                $this->copydir($Entry, $target . '/' . $entry);
                continue;
            }
            @copy($Entry, $target . '/' . $entry);
        }

        $d->close();
    } else {
        copy($source, $target);
    }
    }



}