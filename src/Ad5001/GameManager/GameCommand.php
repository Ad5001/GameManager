<?php
namespace Ad5001\GameManager;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;


class GameCommand extends Command implements PluginIdentifiableCommand {
    
    public function __construct(Main $main, string $name, string $desc, string $usage, array $aliases, Game $game, string $perm){
        parent::__construct($name, $desc, $usage, $aliases);
        $this->setPermission($perm);
        $this->main = $main;
        $this->game = $game;
    }
    public function execute(CommandSender $sender, $label, array $args) {
        return $this->game->onCommand($sender, $this, $label, $args);
    }
}
