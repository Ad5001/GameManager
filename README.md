# GameManager
Make minigames easilier !
Gamemanager is a plugin that allow minigame writing a lot easilier with his easy api, disigned for per world minigame !    
I made this for me at start but feel free to use it to write your own minigame !     
### How to make a game:     
To make a game base, create a new file called "<YOUR_GAME_NAME>.php". Inside it, add a 

``<?php

use Ad5001\GameManager\Game;
use pocketmine\Player;

class <YOUR_GAME_NAME> extends Game {

public function onGameStart() { // When the game start (enought players)
// $this->getServer()->broadcastMessage("Game started on {$this->getLevel()->getName()}");
}


public function onGameStop() { // When you stop the game.
// $this->getServer()->broadcastMessage("Game stoped on {$this->getLevel()->getName()}");
}


public function getName() : string {    
return "<YOUR_GAME_NAME>";   
}   
   

public function getMaxPlayers() : int { // Return the max of the players
return <NUMBER OF PLAYERS MAX>;
}


// Write in progress :)   
}   

``
