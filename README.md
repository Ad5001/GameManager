# GameManager
Make minigames easier!
Gamemanager is a plugin that makes writing a minigame much easier with its easy api, which is conveniently designed for per world minigames!    
I made this for me in the beginning but feel free to use it to write your own minigame!     
### How to make a game:     
To make a game base, create a new file called "<**YOUR_GAME_NAME**>.php". Inside it, add a 

```php    
<?php

use Ad5001\GameManager\Game;
use pocketmine\Player;

class <YOUR_GAME_NAME> extends Game {

public function onGameStart() { // When the game start (enought players)
// $this->getServer()->broadcastMessage("Game started on {$this->getLevel()->getName()}");
}


public function onGameStop() { // When you stop the game.
// $this->getServer()->broadcastMessage("Game stoped on {$this->getLevel()->getName()}");
}
   

public function getMaxPlayers() : int { // Return the max of the players
return <NUMBER OF PLAYERS MAX>;
}
  
}
```    
This is the basic class.    
Methods that you can add to the class (optionals):    


| Function name                     | Arguments                                                                                         | When it is called ?                                                                                           | What does it do by default?                                                                                                                                                 |
|-----------------------------------|---------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| onJoin                            | Player $player                                                                                    | When a player join the game.                                                                                  | It check if there are enought players that joined the game to start or kick the player from the game if there are too much players that have joined and game  isn't started |
| onQuit                            | Player $player                                                                                    | When a player quit the game                                                                                   | Nothing                                                                                                                                                                     |
| onCommand                         | \pocketmine\command\CommandSender $sender, \pocketmine\command\Command $cmd,  $label, array $args | When a command (registered by the game (see  registerCommand in methods)) is used by the console or a player. | Nothing                                                                                                                                                                     |
| onInteract                        | PlayerInteractEvent $event                                                                        | When a player touch a block  and is in the current game                                                       | Nothing                                                                                                                                                                     |
| onChat onPlayerChat               | PlayerChatEvent $event                                                                            | When a player chat and  is in the current game                                                                | Nothing                                                                                                                                                                     |
| onPlayerCommand                   | PlayerCommandPreprocessEvent $event                                                               | When a player use a  command, is in the current  game, and the commmand haven't been processed yet            | Nothing                                                                                                                                                                     |
| onDeath onPlayerDeath             | PlayerDeathEvent $event                                                                           | When a player die and  is in the current game                                                                 | Nothing                                                                                                                                                                     |
| onDrop onPlayerDropItem           | PlayerDropItemEvent $event                                                                        | When a player drop an item and is in the current game                                                         | Nothing                                                                                                                                                                     |
| onPlayerMove onMove               | PlayerMoveEvent $event                                                                            | When a player move in  the current game                                                                       | Nothing                                                                                                                                                                     |
| onPlayerItemConsume onItemConsume | PlayerItemConsumeEvent $event                                                                     | When a player use an item in the current game                                                                 | Nothing                                                                                                                                                                     |
| onPlayerItemHeld onItemHeld       | PlayerItemHeldEvent $event                                                                        | When a player switch item in the current game                                                                 | Nothing                                                                                                                                                                     |
| onDataPacketReceive               | DataPacketReceiveEvent $event                                                                     | When the server receive a packet from a player in the current game                                            | Nothing                                                                                                                                                                     |
| onDataPacketSend                  | DataPacketSendEvent $event                                                                        | When the server send a packet to a player in the current game                                                 | Nothing                                                                                                                                                                     |
| onServerCommand                   | ServerCommandEvent $event                                                                         | When the console or RCon send a command                                                                       | Nothing                                                                                                                                                                     |
| onBlockBreak onBreak              | BlockBreakEvent $event                                                                            | When a player break a block in the current game.                                                              | Nothing                                                                                                                                                                     |
| onBlockPlace onPlace              | BlockPlaceEvent $event                                                                            | When a player place a block in the current game                                                               | Nothing                                                                                                                                                                     |
| onEntityDamage                    | EntityDamageEvent                                                                                 | When an entity get damage in the current game                                                                 | Nothing                                                                                                                                                                     |


Methods that you can use in the class:

| Function name     | Arguments                                                                                          |                                             What does it return ?                                            |
|-------------------|----------------------------------------------------------------------------------------------------|:------------------------------------------------------------------------------------------------------------:|
| getPlugin         | None                                                                                               | Instance of the main class (Ad5001\GameManager\Main)                                                         |
| getLevel          | None                                                                                               | Intance of the current game level (pocketmine\level\Level)                                                   |
| isStarted         | None                                                                                               | Boolean if the game is already started (boolean)                                                             |
| stop              | None                                                                                               | Boolean true. Stop the game.                                                                                 |
| start             | None                                                                                               | Boolean true. Start the game.                                                                                |
| getLogger         | None                                                                                               | Instance of the plugin logger (pocketmine\plugin\PluginLogger)                                               |
| saveDefaultConfig | None                                                                                               | Save a config that you will be able to use using the next function                                           |
| getConfig         | None                                                                                               | Instance of the config (be sure of having used saveDefaultConfig)(pocketmine\utils\Config)                   |
| getDataFolder     | None                                                                                               | The path of the game data folder (string)                                                                    |
| registerCommand   | string $cmd, string $desc, string $usage, array $aliases, string $perm = "gamemanager.command.use" | Null. Register command $cmd with the description $desc, usage $usage, aliases $aliases and permission $perm. |
