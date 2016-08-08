<?php
use Ad5001\GameManager\Game;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class Spleef extends Game {


    public function __construct(string $name, Level $level) {
        parent::__construct($name, $level);
        $this->getLogger()->info("Spleef enabled ! Thanks for choosing spleef and remeber to leave a 'review' on projects.ad5001.ga/GameManager/Spleef/ if you like it :)");
        $text = '# Welcome to Spleef config !\n# Configure here the max stuffs about the spleef game.\n# Example:\n#world:\n#  maxplayers: 8\n#  minplayers: 2\n#  center: [x, z]\n#  SnowBlock: 80\n#  WinCommand: give {winner} diamond\n#  # This is optional but if you want super customisation...\n#  # WinPHPCode: "$this->getServer()->broadcastMessage($this->winner . \' won the game !\');"\n
';
        if(!file_exists($this->getDataFolder() . "/config.yml")) {
            file_put_contents($this->getDataFolder()."/config.yml", $text);
        }
        if($this->getConfig()->get($this->getLevel()->getName()) !== null) {
            $this->getConfig()->set($this->getLevel()->getName(), ["minplayers" => 2, "maxplayers" => 8, "center" =>[0, 0], "SnowBlock" => 80, "WinCommand" => "give {winner} diamond"]);
        }
        $this->doubleJump = [];
        $this->registerCommand("spleef", "Main Spleef command !", "/spleef <subcommand> [parameter]", [], "gamemanager.command.op");
    }


    public function onGameStart() {
        $this->layers = [];
        for($y = 0; $y <= 128; $y++) {
            if($this->getLevel()->getBlock(new Vector3($this->getConfig()->get($this->getLevel()->getName())["center"][0], $y, $this->getConfig()->get($this->getLevel()->getName())["center"][1]))->getId() == $this->getConfig()->get($this->getLevel()->getName())["SnowBlock"]) {
                $this->layers[] = $y;
            }
        }
        foreach($this->getLevel()->getPlayers() as $player) {
            $player->sendMessage("§l§o§6[§r§l§bSpleef§o§6]§r§f Game started ! Break the " . Item::get($this->getConfig()->get($this->getLevel()->getName())["SnowBlock"])->getName() . " with your hand and try to make other players fell of the platform !");
            $e = \pocketmine\entity\Effect::getEffect(3);
            $e->setVisible(false);
            $e->setDuration(999999);
            $e->setAmbient();
            $e->setAmplifier(99);
            $e->setVisible(false);
            $player->addEffect($e);
            $this->tpSafePlayer($player);
            $player->setGamemode(0);
            $i = Item::get(Item::FEATHER, 0, 10);
            $i->setCompoundTag(\pocketmine\nbt\NBT::parseJSON('{display:{Name:"§rDouble jump"},doubleJump:"true"}'));
            $player->getInventory()->addItem($i);
            $this->doubleJump[$player->getName()] = 10;
        }
    }


    public function onInteract(\pocketmine\event\player\PlayerInteractEvent $event) {
        if($event->getPlayer()->getInventory()->getItemInHand()->getId() == Item::FEATHER and $event->getPlayer()->getInventory()->getItemInHand()->hasCompoundTag()) {
            // echo $event->getPlayer()->getInventory()->getItemInHand()->getCompoundTag();
          if(strpos($event->getPlayer()->getInventory()->getItemInHand()->getCompoundTag(), "doubleJump")) {
            $yaw = $event->getPlayer()->yaw;
            if (0 <= $yaw and $yaw < 22.5) {
			      $this->doubleJump($event->getPlayer(), 0, 0, -15040, -0.125);
           } elseif (22.5 <= $yaw and $yaw < 67.5) {
                    $this->doubleJump($event->getPlayer(), 0, 15040, -15040, -0.125);
           } elseif (67.5 <= $yaw and $yaw < 112.5) {
                    $this->doubleJump($event->getPlayer(), 0, 15040, 0, -0.125);
           } elseif (112.5 <= $yaw and $yaw < 157.5) {
                    $this->doubleJump($event->getPlayer(), 0, 15040, 15040, -0.125);
           } elseif (157.5 <= $yaw and $yaw < 202.5) {
                    $this->doubleJump($event->getPlayer(), 0, 0, 15040, -0.125);
           } elseif (202.5 <= $yaw and $yaw < 247.5) {
                    $this->doubleJump($event->getPlayer(), 0, -15040, 15040, -0.125);
           } elseif (247.5 <= $yaw and $yaw < 292.5) {
                   $this->doubleJump($event->getPlayer(), 0, -15040, 0, -0.125);
           } elseif (292.5 <= $yaw and $yaw < 337.5) {
                    $this->doubleJump($event->getPlayer(), 0, -15040, -15040, -0.125);
           } elseif (337.5 <= $yaw and $yaw < 360) {
                    $this->doubleJump($event->getPlayer(), 0, 0, -15040, -0.125);
           }
          }
        }
    }



    public function doubleJump(Player $player, $damage, $x, $z, $base) {
        $f = sqrt(-$x * -$x + -$z * -$z);
		if($f <= 0){
			return;
		}

		$f = 1 / $f;

		$motion = new Vector3($player->motionX, $player->motionY, $player->motionZ);

		$motion->x /= 2;
		$motion->y /= 2;
		$motion->z /= 2;
		$motion->x += -$x * $f * -$base;
		$motion->y = 0.75;
        // echo $motion->y;
		$motion->z += -$z * $f * -$base;

		// if($motion->y > $base){
			// $motion->y = $base + 0.2;
		// }

        if(!isset($this->doubleJump[$player->getName()])) {
            $this->doubleJump[$player->getName()] = 10;
        }
        if($this->doubleJump[$player->getName()] > 0) {
            $player->setMotion($motion);
            $player->getInventory()->getItemInHand()->setCount($this->doubleJump[$player->getName()]);
            $this->doubleJump[$player->getName()]--;
            $player->sendPopup("§c" . $this->doubleJump[$player->getName()] . " double jumps left");
        }
    }



    public function tpSafePlayer(Player $player) {
            $xrand = rand(-3 * count($this->getLevel()->getPlayers()) - 1, 3 * count($this->getLevel()->getPlayers()) - 1);
            $zrand = rand(-3 * count($this->getLevel()->getPlayers()) - 1, 3 * count($this->getLevel()->getPlayers()) - 1);
        
            $v3 = new Vector3($this->getConfig()->get($this->getLevel()->getName())["center"][0] + $xrand, $this->layers[count($this->layers) - 1], $this->getConfig()->get($this->getLevel()->getName())["center"][0] + $xrand);
            if($this->getLevel()->getBlock($v3)->getId() == $this->getConfig()->get($this->getLevel()->getName())["SnowBlock"] and $this->getLevel()->getBlock(new Vector3($v3->x, $v3->y+1, $v3->z))->getId() == 0) {
                $player->teleport(new Vector3($v3->x, $v3->y+1, $v3->z));
                return true;
            } else {
                return $this->tpSafePlayer($player);
            }
    }


    public function onGameStop() {
        // $this->getLogger()->info("Game stoped");
        foreach($this->getLevel()->getPlayers() as $p) {
            $p->setGamemode(0);
            if($p !== $this->winner) {
            $p->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
            $p->sendMessage("§l§o§6[§r§b§lSpleef§o§6]§r§f " . $this->winner->getName() . " won the game ! Teleporting back to spawn...");
            } else {
            $this->winner->sendMessage("§l§o§6[§r§b§lSpleef§o§6]§r§f You won the game ! Teleporting back to spawn...");
            }
            if(isset($this->getConfig()->get($this->getLevel()->getName())["WinCommand"])) {
                $this->getServer()->dispatchCommand(new \pocketmine\command\ConsoleCommandSender(), str_ireplace("{winner}", $this->winner->getName(), $this->getConfig()->get($this->getLevel()->getName())["WinCommand"]));
            }
            if(isset($this->getConfig()->get($this->getLevel()->getName())["WinPHPCode"])) {
                eval($this->getConfig()->get($this->getLevel()->getName())["WinPHPCode"]);
            }
            foreach($this->layers as $y) {
                for($x = -3 * count($this->getMaxPlayers()) + $this->getConfig()->get($this->getLevel()->getName())["center"][0]; $x <= 3 * count($this->getMaxPlayers()) + $this->getConfig()->get($this->getLevel()->getName())["center"][0]; $x++) {
                    for($z = -3 * count($this->getMaxPlayers()) + $this->getConfig()->get($this->getLevel()->getName())["center"][1]; $z <= 3 * count($this->getMaxPlayers()) + $this->getConfig()->get($this->getLevel()->getName())["center"][1]; $z++) {
                        if($this->getLevel()->getBlock(new Vector3($x, $y, $z))->getId() == 0) {
                            $this->getLevel()->setBlock(new Vector3($x, $y, $z), Item::fromString($this->getConfig()->get($this->getLevel()->getName())["SnowBlock"])->getBlock());
                        }
                    }
                }
            }
        }
    }


    public function onJoin(Player $player) {
       if($this->main->getInGamePlayers($this->getLevel()) + 1 >= $this->getMinPlayers() and !$this->isStarted()) {
           $this->getLogger()->info("Started !");
           $h = $this->getServer()->getScheduler()->scheduleRepeatingTask($t = new StartTask($this), 20);
           $t->setHandler($h);
           if(isset($this->task)) {
               $this->getServer()->getScheduler()->cancelTask($this->task->getTaskId());
           }
           $this->task = $t;
       }
       if($this->main->getInGamePlayers($this->getLevel()) + 1 > $this->getMaxPlayers() and !$this->isStarted()) {
           $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
           $player->sendMessage("Too many players already in the game !");
       } elseif(!$this->isStarted()) {
           $player->setGamemode(2);
           $player->getInventory()->clearAll();
       }
       foreach($this->getLevel()->getPlayers() as $p) {
            $p->sendMessage(\pocketmine\utils\TextFormat::YELLOW . $player->getName() . " joined the spleef game. " . ($this->getMinPlayers() - $this->getPlugin()->getInGamePlayers($this->getLevel()) - 1) ." players left before starting !");
       }
       $player->sendMessage(\pocketmine\utils\TextFormat::YELLOW . "You joined the spleef game. " . ($this->getMinPlayers() - $this->getPlugin()->getInGamePlayers($this->getLevel()) - 1) ." players left before starting !");
    }


    public function onQuit(Player $player) {
        if($player->isSurvival() or $player->isAdventure()) {
            if($this->isStarted()) {
                 foreach($this->getLevel()->getPlayers() as $p) {
                     $p ->sendMessage(\pocketmine\utils\TextFormat::YELLOW . $player->getName() . " left the spleef game. {$this->getPlugin()->getInGamePlayers($this->getLevel())} players left !", [$this->getLevel()->getPlayers()]);
                 }
                 $player->sendMessage(\pocketmine\utils\TextFormat::YELLOW . "You left the spleef game. " . ($this->getMinPlayers() - $this->getPlugin()->getInGamePlayers($this->getLevel())) ." players left !");
            } else {
                foreach($this->getLevel()->getPlayers() as $p) {
                     $p ->sendMessage(\pocketmine\utils\TextFormat::YELLOW . $player->getName() . " left the spleef game. {$this->getPlugin()->getInGamePlayers($this->getLevel())} players left before starting !", [$this->getLevel()->getPlayers()]);
                 }
                 $player->sendMessage(\pocketmine\utils\TextFormat::YELLOW . "You left the spleef game. " . ($this->getMinPlayers() - $this->getPlugin()->getInGamePlayers($this->getLevel())) ." players left before starting !");
            }
        }
    }


    public function onPlayerDeath(\pocketmine\event\player\PlayerDeathEvent $event) {
        $event->getPlayer()->setGamemode(3);
        $event->getPlayer()->removeEffect(3);
        if(($this->getPlugin()->getInGamePlayers($this->getLevel()) - 1) == 1) {
            foreach($this->getLevel()->getPlayers() as $p) {
                if($p->isSurvival()) {
                    $this->winner = $p;
                    $p->removeEffect(3);
                    $p->getInventory()->clearAll();
                    $this->stop();
                }
            }
            $this->stop();
        } elseif(($this->getPlugin()->getInGamePlayers($this->getLevel()) - 1) == 0) {
            $this->winner = $event->getPlayer();
            $event->getPlayer()->getInventory()->clearAll();
            $this->stop();
        }
    }


    public function onBreak(\pocketmine\event\block\BlockBreakEvent $event) {
        if($event->getBlock()->getId() !== $this->getConfig()->get($this->getLevel()->getName())["SnowBlock"] or !$event->getPlayer()->isSurvival()) {
            $event->setCancelled();
        } else {
            $event->setDrops([]);
        }
        if($event->getPlayer()->isOp() and !$this->isStarted()) {
            $event->setCancelled(false);
        }
    }


    public function onEntityDamage(\pocketmine\event\entity\EntityDamageEvent $event) {
        if(!in_array($event->getCause(), [7, 11, 12, 13, 14])) {
            $event->setCancelled();
        }
    }


    public function onCommand(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $cmd, $label, array $args) {
        if(strtolower($cmd->getName()) == "spleef") {
            if(isset($args[0])) {
                switch(strtolower($args[0])) {
                    case "start":
                    if(isset($this->gm->getLevels()[$sender->getLevel()->getName()])) {
                        if(!$this->gm->getLevels()[$sender->getLevel()->getName()]->isStarted() and $this->gm->getLevels()[$sender->getLevel()->getName()]->getName() == "Spleef") {
                            $this->start();
                        }
                    }
                    break;
                    case "stop":
                    if(isset($this->gm->getLevels()[$sender->getLevel()->getName()])) {
                        if($this->gm->getLevels()[$sender->getLevel()->getName()]->isStarted() and $this->gm->getLevels()[$sender->getLevel()->getName()]->getName() == "Spleef") {
                            $this->winner = $sender;
                            $this->stop();
                        }
                    }
                    break;
                }
            }
        }
    }


    public function getMinPlayers() : int {
        // echo $this->getConfig()->get($this->getLevel()->getName())["minplayers"] . PHP_EOL;
        return $this->getConfig()->get($this->getLevel()->getName())["minplayers"];
    }


    public function getMaxPlayers() : int {
        //  echo $this->getConfig()->get($this->getLevel()->getName())["maxplayers"] . PHP_EOL;
        return $this->getConfig()->get($this->getLevel()->getName())["maxplayers"];
    }


    public function getName() : string {
        return "Spleef";
    }
}

class StartTask extends \pocketmine\scheduler\PluginTask {

    public function __construct(Spleef $main) {
        parent::__construct($main->getPlugin());
        $this->seconds = 0;
        $this->main = $main;
    }


    public function onRun($tick) {
        if($this->getOwner()->getInGamePlayers($this->main->getLevel()) < $this->main->getMinPlayers()) {
            $this->main->getServer()->broadcastMessage("Start cancelled ! Not enought players to start !");
            $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
        } else {
            switch($this->seconds) {
                case 0:
                case 10:
                case 20:
                case 25:
                case 26:
                case 27:
                case 28:
                case 29:
                foreach($this->main->getLevel()->getPlayers() as $p) {
                    $p->sendMessage("§l§o§6[§r§b§lSpleef§o§6]§r§f " . strval(30 - $this->seconds) . " seconds left before the game starts !");
                }
                break;
                case 30:
                $this->main->start();
                $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
                break;
            }
            $this->seconds++;
        }
    }
}