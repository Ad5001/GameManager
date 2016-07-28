<?php
namespace Ad5001\GameManager;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Level;

use Ad5001\GameManager\Main;



class GameManager {


   public function __construct(Main $main) {
        $this->main = $main;
        $this->server = $main->getServer();
        $files = array_diff(scandir($this->getDataFolder() . "/games"), [".", ".."]);
        $this->games = [];
        $this->levels = [];
        $this->startedgames = [];
        foreach ($files as $file) {
            require($file);
            $classn = getClasses(file_get_contents($this->getDataFolder() . "/games/" . $file));
            $this->games[explode(".php", $file)[0]] = $classn;
            @mkdir($this->main->getDataFolder() . "games/$classn");
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



    public function registerLevel(Level $level, string $game) {
        if(!array_key_exists($level->getName(), $this->levels)) {
            if(isset($this->games[$game])) {
                $this->levels[$level->getName()] = new $this->games[$game]($level);
            } else {
                $this->main->getLogger()->warn("No game found with name $game");
            }
        }
    }



    public function reloadLevel(Level $level) {
        $this->backup($level);
        rrmdir($this->main->getFilePath() . "worlds/{$level->getName()}");
        $this->restore($level);
    }


    private function rrmdir($dir) { // This is from PHP.NET
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
    
    
    private function copydir( $source, $target ) {
    if ( is_dir( $source ) ) {
        @mkdir( $target );
        $d = dir( $source );
        while ( FALSE !== ( $entry = $d->read() ) ) {
            if ( $entry == '.' || $entry == '..' ) {
                continue;
            }
            $Entry = $source . '/' . $entry; 
            if ( is_dir( $Entry ) ) {
                full_copy( $Entry, $target . '/' . $entry );
                continue;
            }
            copy( $Entry, $target . '/' . $entry );
        }

        $d->close();
    } else {
        copy( $source, $target );
    }
    }



}