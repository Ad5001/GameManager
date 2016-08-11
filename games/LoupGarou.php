<?php
/*
This software is distributed under the BoxOfDevs Public General License 1.1
Some names could come from the Werewolfs of Thiercelieux by (c) Lui-même Co Ltd 2016
@author Ad5001
@link projects.ad5001.ga/GameManager
@version 1.0
*/
use Ad5001\GameManager\Game;
use pocketmine\Player;
use pocketmine\item\Item;

class LoupGarou extends Game {

    public function onGameStart() {
        $desc = ["Sorcière" => "Vous êtes dans le camp des villageois.\nVous avez une potion de vie et une potion de mort que vous devez utiliser à l'avantage des villageois.", "Voyante" => "Vous êtes dans le camp des villageois.\nChaque nuit, vous pouvez voir ce qu'un joueur est.", "Echangeur" => "Vous n'avez pas de camp (pour le moment).\nAu premier tour, vous echangerez votre role avec un autre joueur\net vous prendrez ainsi tout ses attributs (camps + abilités)", "Meutrier" => "Vous êtes désormais dans le camp des villageois.\nDès que vous mourrez, vous pouvez tuer une personne !", "Venus" => "Vous êtes dans le camp des villageois.\nAu premier tour, vous désignerez 2 amoureux qui seront dans un camp à part qui est eux contre tous.", "Courageuse" => "Vous êtes dans le camp des villageois.\nChaque nuit, vous pourez espionner les loups garous ! Mais faites attention à ne pas vous faire repérer !", "Villageois" => "Vous êtes (bien sur) dans le camp des villageois.\nComme tout les autres joueurs, vous pourez voter au villagepour quelle personne meurs.", "LoupGarou" => "Vous êtes dans le camp des loup garou.\nTuez tout les villageois pour remporter la partie !"];
        $roles = ["Sorcière", "Voyante", "Echangeur", "Meutrier", "Venus", "Courageuse"];
        for($i = 0; $i <= count($this->getInGamePlayers()) - 10; $i++) {
            $roles[] = "Villageois";
        }
        for($i = 0; $i <= round(count($this->getInGamePlayers())); $i++) {
            $roles[] = "LoupGarou";
        }
        foreach($this->getInGamePlayers() as $p) {
            $res = array_rand($roles);
            $r = $roles[$res];
            if(!isset($this->{$r})) {
                if($r !== "LoupGarou" or $r !== "Villageois") {
                    $this->{$r} = $p;
                } else {
                    $this->{$r} = [$p];
                }
            } else {
                $this->{$r}[] = $p;
            }
            $this->{$r} = $p;
            unset($roles[$res]);
            $p->sendTip("§eVous êtes...\n\n\n§4§l" . $r . " !\n\n§7" . $desc[$r]);
        }
        $this->amoureux = [];
        $this->voted = [];
        $this->hasvoted = [];
        $this->current = null;
        $this->killed = null;
        $this->maire = null;
        $this->potions = ["life" => true, "death" => true, "speed" => true, "slow" => true, "regen" => true, "poison" => true];
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new FirstDayTask($this), 20);
    }


    public function onGameStop() {
        foreach($this->getLevel()->getPlayers() as $p) {
            $p->setGamemode(0);
            $p->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
        }
    }


    public function onJoin(Player $player) {
       if($this->main->getInGamePlayers($this->getLevel()) + 1 >= $this->getMinPlayers() and !$this->isStarted()) {
           $this->getLogger()->info("Started !");
           $h = $this->getServer()->getScheduler()->scheduleRepeatingTask($t = new StartLGTask($this), 20);
           $t->setHandler($h);
           if(isset($this->task)) {
               $this->getServer()->getScheduler()->cancelTask($this->task->getTaskId());
           }
           $this->task = $t;
       }
       if($this->main->getInGamePlayers($this->getLevel()) + 1 > $this->getMaxPlayers() and !$this->isStarted()) {
           $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
           $player->sendMessage("Il y a deja trop de joueur !");
       } elseif(!$this->isStarted()) {
           $player->setGamemode(2);
           $player->getInventory()->clearAll();
       }
       foreach($this->getLevel()->getPlayers() as $p) {
            $p->sendMessage(\pocketmine\utils\TextFormat::YELLOW . $player->getName() . " a rejoint le loup garou. Il reste " . ($this->getMinPlayers() - $this->getPlugin()->getInGamePlayers($this->getLevel()) - 1) ." avant de demarer la partie !");
       }
       $player->sendMessage(\pocketmine\utils\TextFormat::YELLOW . "Vous avez rejoint le loup garou. Il reste " . ($this->getMinPlayers() - $this->getPlugin()->getInGamePlayers($this->getLevel()) - 1) ." avant de demarer la partie !");
       $player->sendMessage("Le loup garou est un jeu qui a été crée par les studios (c) Lui-meme. Co . Ce titre n'est en aucun cas affilié aux créateurs ou à Lui-meme Co Ltd . Merci de votre comprehension."); // Laissez cette ligne si vous ne voulez pas de problème de droit d'auteur.
    }


    public function onQuit(Player $player) {
        if($player->isSurvival() or $player->isAdventure()) {
            if($this->isStarted()) {
                 foreach($this->getLevel()->getPlayers() as $p) {
                     $p ->sendMessage(\pocketmine\utils\TextFormat::YELLOW . $player->getName() . " a quitté le loup garou. Il reste " . $this->getMinPlayers() - $this->getPlugin()->getInGamePlayers($this->getLevel()) . " joueurs !", [$this->getLevel()->getPlayers()]);
                 }
                 $player->sendMessage(\pocketmine\utils\TextFormat::YELLOW . "Vous avez quitté le loup garou. Il reste " . ($this->getMinPlayers() - $this->getPlugin()->getInGamePlayers($this->getLevel())) ." joueurs !");
            } else {
                foreach($this->getLevel()->getPlayers() as $p) {
                     $p ->sendMessage(\pocketmine\utils\TextFormat::YELLOW . $player->getName() . " a quitté le loup garou. Il reste " . $this->getMinPlayers() - $this->getPlugin()->getInGamePlayers($this->getLevel()) . "joueurs avant de demarer la partie !", [$this->getLevel()->getPlayers()]);
                 }
                 $player->sendMessage(\pocketmine\utils\TextFormat::YELLOW . "Vous avez quitté le loup garou. Il reste " . ($this->getMinPlayers() - $this->getPlugin()->getInGamePlayers($this->getLevel())) ." joueurs avant de demarer la partie !");
            }
        }
    }


    public function onPlayerDeath(\pocketmine\event\player\PlayerDeathEvent $event) {
        $event->setDeathMessage("");
        $this->broadcastMessage($event->getPlayer()->getName() . " est mort ! C'était un " . $this->getRole($player));
        $this->eliminate($event->getPlayer());
    }


    public function finish(bool $b) {
        if($b) {
            $l = $this->LoupGarou[count($this->LoupGarou) - 1];
            unset($this->LoupGarou[count($this->LoupGarou) - 1]);
            $n = [];
            foreach($this->LoupGarou as $lg) {
                $n[] = $lg->getName();
            }
            foreach($this->getLevel()->getPlayers() as $p) {
                $p->sendTip("§l" . (count($this->LoupGarou) == 0 ? implode(", ", $n) . " et " . $l->getName() : $l->getName()) . " (Loups Garous) ont gagnés la partie car tout les villageois sont mort ! \n\n\n§rRetour au lobby dans 30 secondes...");
            }
        } else {
            $v = $this->main->getInGamePlayers($this->getLevel());
            $l = $v[count($v) - 1];
            unset($v[count($v) - 1]);
            $n = [];
            foreach($v as $lg) {
                $n[] = $v->getName();
            }
            foreach($this->getLevel()->getPlayers() as $p) {
                $p->sendTip("§l" . (count($v) == 0 ? implode(", ", $n) . " et " . $l->getName() : $l->getName()) . " (Villageois) ont gagnés la partie car tout les loups garous sont mort ! \n\n\n§rRetour au lobby dans 30 secondes...");
            }
        }
        $this->getServer()->getScheduler()->scheduleDelayedTask(new FinishLGTask($this), 600);
    }



    public function onEntityDamage(\pocketmine\event\entity\EntityDamageEvent $event) {
        if($event instanceof \pocketmine\event\entity\EntityDamageByEntityEvent) {
            if($event->getEntity() instanceof Player) {
                switch(strtolower($this->current)) {
                    case "echangeur":
                    if($event->getDamager() instanceof Player) {
                      if($this->getRole($event->getDamager()) == "Echangeur") {
                        $this->{$this->getRole($event->getEntity())} = $event->getDamager();
                        $this->Echangeur = $event->getEntity();
                        $event->getEntity()->sendTip("§eVous êtes désormais...\n\n\n§4§lEchangeur !\n\n§7Sauf que maintenant, vous ne pouvez plus voler :p");
                        $event->getDamager()->sendTip("§eVous êtes désormais...\n\n\n§4§l" . $this->getRole($event->getDamager()) . " !\n\n§7" . $desc[$this->getRole($event->getDamager())]);
                        $this->task->turn = 67;
                        $this->current = null;
                      }
                    }
                    $event->setCancelled();
                    break;
                    case "venus":
                    if($e->getCause() === EntityDamageEvent::CAUSE_PROJECTILE){
                        $projectile = $e->getDamager();
                        if($projectile instanceof \pocketmine\entity\Arrow){
                            $this->amoureux[] = $event->getEntity();
                            if(count($this->amoureux) == 2) {
                                $this->amoureux[0]->sendTip("You're now loving " . $this->amoureux[1]->getName() . " . It's now you beside the whole world. If one of you die, you die too !");
                                $this->amoureux[1]->sendTip("You're now loving " . $this->amoureux[0]->getName() . " . It's now you beside the whole world. If one of you die, you die too !");
                                $this->task->turn = 132;
                                $this->current = null;
                            }
                        }
                    }
                    $event->setCancelled();
                    break;
                    case "voyante":
                    if($event->getDamager() instanceof Player) {
                      if($this->getRole($event->getDamager()) == "Voyante") {
                          $event->getDamager()->sendTip($event->getEntity() . " est " . $this->getRole($event->getEntity()) . " !");
                          $this->task->turn = 200;
                          $this->current = null;
                      }
                    }
                    $event->setCancelled();
                    break;
                    case "lg":
                    if($event->getDamager() instanceof Player) {
                      if($this->getRole($event->getDamager()) == "LoupGarou" and $this->getRole($event->getEntity()) !== "LoupGarou") {
                          if($event->getEntity()->getHeal() <= 7) {
                              foreach($this->LoupGarou as $lg) {
                                  $lg->sendTip("Vous avez tué " . $event->getEntity()->getName() .".");
                                  $this->killed = $event->getEntity();
                                  foreach($this->getLevel()->getPlayers() as $p) {
                                      $p->hidePlayer($event->getEntity());
                                  }
                              }
                              $this->task->turn = 325;
                          }
                          $this->current = null;
                      } elseif($this->getRole($event->getDamager()) == "Courageuse" and $this->getRole($event->getEntity()) == "LoupGarou") {
                          $event->getEntity()->addEffect(\pocketmine\entity\Effect::getEffect(2)->setAmplifier(99)->setDuration(200));
                          $event->getDamager()->sendMessage($event->getEntity()->getName() . " a été ralenti !");
                          $event->setCancelled();
                      } else {
                          $event->setCancelled();
                      }
                    }
                    break;
                    case "sorcière":
                    if($event->getDamager() instanceof Player) {
                      if($this->getRole($event->getDamager()) == "Sorcière" and $this->getRole($event->getEntity()) !== "Sorcière") {
                          if($event->getDamager()->getInventory()->getItemInHand()->getId() == 373) {
                              if($event->getDamager()->getInventory()->getItemInHand()->getDamage() == 23) {
                                  $this->killed2 = $event->getEntity();
                                  $this->potions["death"] = false;
                                  $this->task->turn = 390;
                              } elseif($event->getDamager()->getInventory()->getItemInHand()->getDamage() == 14) {
                                  $this->speed = $event->getEntity();
                                  $this->potions["speed"] = false;
                                  $this->task->turn = 390;
                              } elseif($event->getDamager()->getInventory()->getItemInHand()->getDamage() == 17) {
                                  $this->slow = $event->getEntity();
                                  $this->potions["slowness"] = false;
                                  $this->task->turn = 390;
                              } elseif($event->getDamager()->getInventory()->getItemInHand()->getDamage() == 28) {
                                  $this->regen = $event->getEntity();
                                  $this->potions["regen"] = false;
                                  $this->task->turn = 390;
                              } elseif($event->getDamager()->getInventory()->getItemInHand()->getDamage() == 25) {
                                  $this->poison = $event->getEntity();
                                  $this->potions["poison"] = false;
                                  $this->task->turn = 390;
                              }
                          }
                      }
                    }
                    $event->setCancelled();
                    break;
                    case "meurtrier":
                    if($event->getDamager() instanceof Player) {
                      if($this->getRole($event->getDamager()) == "Meurtrier") {
                          $this->broadcastMessage($event->getEntity()->getName() . " a été éliminé et c'etait un " . $this->getRole($event->getEntity()));
                          $this->eliminate($event->getEntity());
                      }
                    }
                    break;
                    case "maire":
                    if(!in_array($event->getDamager(), $this->hasvoted)) {
                        $this->hasvoted[] = $event->getDamager();
                        if(isset($this->voted[$event->getEntity()->getName()])) {
                            $this->voted[$event->getEntity()->getName()]++;
                        } else {
                            $this->voted[$event->getEntity()->getName()] = 1;
                        }
                        if(count($this->hasvoted) == count($this->getLevel()->getPlayers())) {
                            $this->task->turn = 525;
                        }
                    }
                    break;
                    case "vote":
                    $this->hasvoted = [];
                    $this->voted = [];
                    if(!in_array($event->getDamager(), $this->hasvoted)) {
                        $this->hasvoted[] = $event->getDamager();
                        $votec = 1;
                        if($event->getDamager() == $this->maire) {
                            $votec = 1.5;
                        }
                        if(isset($this->voted[$event->getEntity()->getName()])) {
                            $this->voted[$event->getEntity()->getName()] += $votec;
                        } else {
                            $this->voted[$event->getEntity()->getName()] = $votec;
                        }
                        if(count($this->hasvoted) == count($this->getLevel()->getPlayers())) {
                            $this->task->turn = 590;
                        }
                    }
                    $event->setCancelled();
                    break;
                    case "kill":
                    if($this->mort !== $event->getDamager() or $this->mort !== $event->getEntity()) {
                        $event->setCancelled();
                    }
                    break;
                    case "successeur":
                    if($event->getDamager() == $this->maire) {
                        $this->maire = $event->getEntity();
                        $this->broadcastMessage("Le nouveau maire est " . $event->getEntity()->getName() . " !");
                        $this->eliminate($event->getDamager());
                        $this->task->turn = 465;
                    }
                    break;
                    default:
                    $event->setCancelled();
                    break;
                }
            }
        }
    }


    public function eliminate(Player $player) {
        if($this->current == "kill") {
            $this->task->turn = 650;
        }
        if($player == $this->amoureux[0] and !isset($this->killedAm)) {
            $this->broadcastMessage("Mais " . $this->amoureux[1]->getName() . " était amoureux de " . $this->amoureux[0]->getName() . " et se suicida par chagrin d'amour...");
            $this->killedAm = true;
            $this->eliminate($this->amoureux[1]);
        }
        if($player == $this->amoureux[1] and !isset($this->killedAm)) {
            $this->broadcastMessage("Mais " . $this->amoureux[0]->getName() . " était amoureux de " . $this->amoureux[1]->getName() . " et se suicida par chagrin d'amour...");
            $this->killedAm = true;
            $this->eliminate($this->amoureux[0]);
        }
        if($this->maire == $player) {
            $this->oldMaire = $player;
            $this->current = "successeur";
            $this-broadcastMessage("Le maire est mort ! Il va désigner son successeur !");
            return true;
        }
        $player->setGamemode(3);
        switch($this->getRole($player)) {
            case "LoupGarou":
            $this->broadcastMessage("Plus que " . count($this->LoupGarou) . " loups garous restants !");
            foreach($this->LoupGarou as $key => $lg) {
                if($lg == $player) {
                    unset($this->LoupGarou[$key]);
                }
            }
            break;
            case "Villageois":
            foreach($this->Villageois as $key => $v) {
                if($v == $player) {
                    unset($this->Villageois[$v]);
                }
            }
            break;
            default:
            unset($this->{$this->getRole($player)});
            break;
        }
        $lg = [];
        $v = [];
        foreach($this->getLevel()->getPlayers() as $p) {
            if($this->getRole($player) == "LoupGarou") {
                array_push($lg, $player);
            } else {
                array_push($v, $player);
            }
        }
        if(count($v) == 0) { // Les loups garous ont gagnés
            $this->finish(true);
        }
        if(count($lg) == 0) { // Les villageois ont gagné
            $this->finish(false);
        }
    }



    public function getRole(Player $player) {
        if(in_array($player, $this->LoupGarou)) {
            return "LoupGarou";
        }
        if(in_array($player, $this->Villageois)) {
            return "Villageois";
        }
        switch($player) {
            case $this->Echangeur:
            return "Echangeur";
            break;
            case $this->Venus:
            return "Venus";
            break;
            case $this->Voyante:
            return "Voyante";
            break;
            case $this->Sorcière:
            return "Sorcière";
            break;
            case $this->Meutrier:
            return "Meutrier";
            break;
            case $this->Courageuse:
            return "Courageuse";
            break;
            default:
            return null;
            break;
        }
    }


    public function onInteract(\pocketmine\event\player\PlayerInteractEvent $event) {
        if($event->getPlayer()->getInventory()->getItemInHand()->getId() == 373) {
            if($event->getPlayer()->getInventory()->getItemInHand()->getDamage() == 21) {
                $this->task->turn = 390;
                foreach($this->getLevel()->getPlayers() as $p) {
                    $p->showPlayer($this->killed);
                }
                $this->killed = null;
                $this->potions["life"] = false;
            }
            $event->setCancelled();
        }
    }



    public function getName() : string {
        return "LoupGarou";
    }


    public function getMinPlayers() : int {
        return 10;
    }


    public function getMaxPlayers() : int {
        return 23;
    }
}


class FinishLGTask extends \pocketmine\scheduler\PluginTask {

    public function __construct(LoupGarou $main) {
        parent::__construct($main->getPlugin());
        $this->main = $main;
    }


    public function onRun($tick) {
        $this->main->stop();
    }


}


class StartLGTask extends \pocketmine\scheduler\PluginTask {

    public function __construct(LoupGarou $main) {
        parent::__construct($main->getPlugin());
        $this->seconds = 0;
        $this->main = $main;
    }


    public function onRun($tick) {
        if($this->getOwner()->getInGamePlayers($this->main->getLevel()) < $this->main->getMinPlayers()) {
            $this->main->getServer()->broadcastMessage("Démarage du jeu annulé ! Il n'y a plus assez de joueurs !");
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
                    $p->sendMessage("§l§o§b[Loup Garou]§r§f " . strval(30 - $this->seconds) . " secondes avant que le jeu demare !");
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

class FirstDayTask extends \pocketmine\scheduler\PluginTask {

    public function __construct(LoupGarou $main) {
        parent::__construct($main->getPlugin());
        $this->turn = 0;
        $this->main = $main;
    }


    public function onRun($tick) {
        switch($this->turn) {
            case 1:
            $this->main->broadcastMessage("Dans le village de Thiercelieux, les villageois se trouvent confrontés à un problème: quelques villageois, chaque nuit, se transforment en loup garou, et font une nouvelle victime chaque nuit ! Les villageois doivent trouver les loup garou afin de les eliminer au vote du village, chaque matin.");
            break;
            case 5:
            $this->main->broadcastMessage("La nuit tombe sur le petit village de Thiercelieux...");
            $this->main->getLevel()->setTime(9000);
            break;
            case 7:
            $this->main->getLevel()->setTime(14000);
            $this->main->broadcastMessage("Tout les villageois s'endorment...");
            $e = \pocketmine\entity\Effect::getEffectByName("BLINDNESS");
            $e->setDuration(9999999);
            $e->setAmbient();
            $e->setVisible(false);
            $e->setAmplifier(3);
            foreach($this->getInGamePlayers() as $p) {
                $p->addEffect($e);
            }
            break;
            case 10:
            $this->main->broadcastMessage("L' Echangeur se réveille !");
            $this->main->Echangeur->removeEffectByName("BLINDNESS");
            $this->main->current = "echangeur";
            $this->main->Echangeur->sendMessage("Touchez le joueur dont vous voulez voler l'identiter. Vous avez une minute puis vous deviendrez villageois.");
            break;
            case 70:
            $e = \pocketmine\entity\Effect::getEffectByName("BLINDNESS");
            $e->setDuration(9999999);
            $e->setAmbient();
            $e->setVisible(false);
            $e->setAmplifier(3);
            foreach($this->main->getInGamePlayers() as $p) {
                $p->addEffect($e);
            }
            $this->main->broadcastMessage("L' Echangeur se rendort...");
            break;
            case 75:
            $this->main->broadcastMessage("Venus se réveille !");
            $this->main->Venus->removeEffectByName("BLINDNESS");
            $this->main->current = "venus";
            $this->main->Venus->addItem(Item::get(item::BOW, 0, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Arc de Venus'},Unbreakable:1}")));
            $this->main->Venus->addItem(Item::get(item::ARROW, 21, 2)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Flèche d'amour\\n \\n \\n \\n'},Unbreakable:1}")));
            $this->main->Venus->sendMessage("Touchez les joueurs que vous vous voulez rendre villageois. Vous avez une minute puis vous deviendrez villageois.");
            break;
            case 135:
            $e = \pocketmine\entity\Effect::getEffectByName("BLINDNESS");
            $e->setDuration(9999999);
            $e->setAmbient();
            $e->setVisible(false);
            $e->setAmplifier(3);
            $this->main->Venus->getInventory()->clearAll();
            foreach($this->main->getInGamePlayers() as $p) {
                $p->addEffect($e);
            }
            $this->main->broadcastMessage("Venus se rendort...");
            break;
            case 140:
            $this->main->broadcastMessage("La voyante se réveille !");
            $this->main->Voyante->removeEffectByName("BLINDNESS");
            $this->main->current = "voyante";
            $this->main->Voyante->sendMessage("Touchez le joueur dont vous voulez rendre villageois connaitre l'identité. Vous avez une minute donc decidez vous vite !");
            break;
            case 200:
            $e = \pocketmine\entity\Effect::getEffectByName("BLINDNESS");
            $e->setDuration(9999999);
            $e->setAmbient();
            $e->setVisible(false);
            $e->setAmplifier(3);
            foreach($this->main->getInGamePlayers() as $p) {
                $p->addEffect($e);
            }
            $this->main->broadcastMessage("La voyante se rendort...");
            break;
            case 205:
            $this->main->broadcastMessage("Les loups garous se réveillent !");
            foreach($this->main->LoupGarou as $lg) {
                $lg->removeEffectByName("BLINDNESS");
                $lg->sendMessage("Tuez un joueur pour le tuer dans le jeu !");
                $lg->getInventory()->addItem(Item::get(Item::DIAMOND_SWORD, 0, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Dent de loup garou'},Unbreakable:1}")));
            }
            $this->main->Courageuse->removeEffectByName("BLINDNESS");
            $this->main->Courageuse->sendMessage("Les loups garous vont faire une nouvelle victime ! Soyez discret(e), espionnez les et tapez les pour les ralentir !");
            $this->main->current = "lg";
            break;
            case 325:
            $e = \pocketmine\entity\Effect::getEffectByName("BLINDNESS");
            $e->setDuration(9999999);
            $e->setAmbient();
            $e->setVisible(false);
            $e->setAmplifier(3);
            foreach($this->main->LoupGarou as $lg) {
                $lg->getInventory()->clearAll();
            }
            foreach($this->main->getInGamePlayers() as $p) {
                $p->addEffect($e);
            }
            $this->main->broadcastMessage("Les loups garou se rendorment...");
            break;
            case 330:
            $this->main->broadcastMessage("La sorcière se réveille !");
            $this->main->Sorcière->removeEffectByName("BLINDNESS");
            $this->main->Sorcière->sendMessage($this->main->killed->getName() . " a été tué cette nuit ! Souaitez vous le resuciter (boire la potion de vie), ou tuer une autre personne (la taper avec la potion de mort). Faites attention, ces potions n'ont qu'un seul usage dans la partie.");
            if($this->potions["life"]) {
                $this->main->Sorcière->getInventory()->addItem(Item::get(Item::POTION, 21, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Potion de vie\\n \\n \\n \\n'},Unbreakable:1}")));
            }
            if($this->potions["death"]) {
                $this->main->Sorcière->getInventory()->addItem(Item::get(Item::POTION, 23, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Potion de mort\\n \\n \\n \\n'},Unbreakable:1}")));
            }
            $this->main->current = "sorcière";
            break;
            case 390:
            $e = \pocketmine\entity\Effect::getEffectByName("BLINDNESS");
            $e->setDuration(9999999);
            $e->setAmbient();
            $e->setVisible(false);
            $e->setAmplifier(3);
            foreach($this->main->getInGamePlayers() as $p) {
                $p->addEffect($e);
            }
            $this->main->broadcastMessage("La sorcière se rendort...");
            break;
            case 395:
            $this->main->broadcastMessage("Le village se reveille...");
            $this->main->getLevel()->setTime(23000);
            break;
            case 400:
            $this->main->getLevel()->setTime(0);
            if(isset($this->main->killed) and isset($this->main->killed2)) {
                $this->main->broadcastMessage("Cette nuit, 2 personnes sont morte. Le " . $this->getRole($this->main->killed) . " et le " . $this->getRole($this->main->killed2) . " !");
                switch($this->getRole($this->main->killed)) {
                    case "Meutrier":
                    $this->main->broadcastMessage("Mais le meutrier, dans sa chute, a tué une autre personne.");
                    $this->main->Meurtier->sendMessage("Choisissez une persone à tuer en la tapant.");
                    $m = true;
                    break;
                }
                switch($this->getRole($this->main->killed2)) {
                    case "Meutrier":
                    $this->main->broadcastMessage("Mais le meutrier, dans sa chute, a tué une autre personne.");
                    $this->main->Meurtier->sendMessage("Choisissez une persone à tuer en la tapant.");
                    $m = true;
                    break;
                    if(!isset($m)) $this->turn = 465; else $this->main->current = "meutrier";
                }
            } elseif(isset($this->main->killed)) {
                $this->main->broadcastMessage("Cette nuit, 1 personne est morte. Le " . $this->getRole($this->main->killed) . " !");
                switch($this->getRole($this->main->killed)) {
                    case "Meutrier":
                    $this->main->broadcastMessage("Mais le meutrier, dans sa chute, a tué une autre personne.");
                    $this->main->Meurtier->sendMessage("Choisissez une persone à tuer en la tapant.");
                    $this->main->current = "meurtrier";
                    break;
                    default:
                    $this->turn = 465;
                    break;
                }
            } elseif(isset($this->main->killed2)) {
                $this->main->broadcastMessage("Cette nuit, 1 personne est morte. Le " . $this->getRole($this->main->killed2) . " !");
                switch($this->getRole($this->main->killed2)) {
                    case "Meutrier":
                    $this->main->broadcastMessage("Mais le meutrier, dans sa chute, a tué une autre personne.");
                    $this->main->Meurtier->sendMessage("Choisissez une persone à tuer en la tapant.");
                    $this->main->current = "meurtrier";
                    break;
                    default:
                    $this->turn = 465;
                    break;
                }
            } else {
                $this->main->broadcastMessage("Cette nuit, Personne n'est mort ! C'est un miracle !");
            }
            break;
            case 465:
            $this->main->broadcastMessage("Il va faloir élire un maire ! Votez pour qu'un joueur soit maire en lui tapant dessus. Le maire tranche si il y a un debat au moment de tuer un suspect (chaque jour)");
            $this->main->current = "maire";
            break;
            case 525:
            $uppestVote = 0;
            foreach($this->main->voted as $running) {
                if($running > $uppestVote) {
                    $uppestVote = $running;
                }
            }
            $runnings = [];
            foreach($this->main->voted as $name => $running) {
                if($running == $uppestVote) {
                    $runnings[] = $name;
                }
            }
            if(count($runnings) > 1) {
                $last = $runnings[count($runnings) - 1];
                unset($runnings[count($runnings) - 1]);
                $this->main->broadcastMessage(implode(", " . $runnings . " et $last ont le même resultat ! Le maire sera séléctionné parmis ceux ci aux hasard."));
                $this->main->maire = $this->main->getServer()->getPlayer($runnings(rand(0, count($runnings))));
                $this->main->broadcastMessage("Le nouveau maire est : {$this->main->maire->getName()} !");
            }
            break;
            case 530:
            $this->main->broadcastMessage("Maintenant, nous devons choisir un personne qui doit mourir. Tapez la personne que vous trouvez la plus suspecte !");
            $this->main->current = "vote";
            break;
            case 590:
            $uppestVote = 0;
            foreach($this->main->voted as $running) {
                if($running > $uppestVote) {
                    $uppestVote = $running;
                }
            }
            $runnings = [];
            foreach($this->main->voted as $name => $running) {
                if($running == $uppestVote) {
                    $runnings[] = $name;
                }
            }
            if(count($runnings) > 1) {
                $last = $runnings[count($runnings) - 1];
                unset($runnings[count($runnings) - 1]);
                $this->main->broadcastMessage(implode(", " . $runnings) . " et $last ont le même resultat ! Vous pourez tuer celui qui sera tiré au hasard sur ceux ci.");
                $this->main->mort = $this->main->getServer()->getPlayer($runnings(rand(0, count($runnings))));
                $this->main->broadcastMessage("La personne mise à mort est {$this->main->mort->getName()} ! Tuez la !");
                foreach($this->main->getLevel()->getPlayers() as $p) {
                    $p->addItem(Item::get(Item::IRON_SWORD)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{Unbreakable:1}")));
                }
                $this->main->mort->sendMessage("Ils veulent votre mort ! Fuyez ou tuez vos enemies !");
                $this->main->current = "kill";
            }
            break;
            case 650:
            foreach($this->main->getLevel()->getPlayers() as $p) {
                $p->getInventory()->clearAll();
            }
            $this->main->task = new DayTask($this->main);
            $h = $this->main->getServer()->getScheduler()->scheduleRepeatingTask($this->main->task, 20);
            $this->main->task->setHandler($h);
            $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
            break;
        }
        $this->turn++;
    }
}

class DayTask extends \pocketmine\scheduler\PluginTask {

    public function __construct(LoupGarou $main) {
        parent::__construct($main->getPlugin());
        $this->turn = 0;
        $this->main = $main;
    }


    public function onRun($tick) {
        switch($this->turn) {
            case 1:
            $this->main->broadcastMessage("Après une longe journée de travail dure, les villageois partent dormir...");
            break;
            case 5:
            $this->main->broadcastMessage("La nuit tombe sur le petit village de Thiercelieux...");
            $this->main->getLevel()->setTime(9000);
            break;
            case 7:
            $this->main->getLevel()->setTime(14000);
            $this->main->broadcastMessage("Tout les villageois s'endorment...");
            $e = \pocketmine\entity\Effect::getEffectByName("BLINDNESS");
            $e->setDuration(9999999);
            $e->setAmbient();
            $e->setVisible(false);
            $e->setAmplifier(3);
            foreach($this->getInGamePlayers() as $p) {
                $p->addEffect($e);
            }
            break;
            case 10:
            $this->turn = 140; // Skip parts of the night so I do not have to rewrite the entitydamage to be 2 times different.
            break;
            case 140:
            if(isset($this->main->Voyante)) {
                $this->main->broadcastMessage("La voyante se réveille !");
                $this->main->Voyante->removeEffectByName("BLINDNESS");
                $this->main->current = "voyante";
                $this->main->Voyante->sendMessage("Touchez le joueur dont vous voulez rendre villageois connaitre l'identité. Vous avez une minute donc decidez vous vite !");
            } else {
                $this->turn = 205;
            }
            break;
            case 200:
            $e = \pocketmine\entity\Effect::getEffectByName("BLINDNESS");
            $e->setDuration(9999999);
            $e->setAmbient();
            $e->setVisible(false);
            $e->setAmplifier(3);
            foreach($this->main->getInGamePlayers() as $p) {
                $p->addEffect($e);
            }
            $this->main->broadcastMessage("La voyante se rendort...");
            break;
            case 205:
            $this->main->broadcastMessage("Les loups garous se réveillent !");
            foreach($this->main->LoupGarou as $lg) {
                $lg->removeEffectByName("BLINDNESS");
                $lg->sendMessage("Tuez un joueur pour le tuer dans le jeu !");
                $lg->getInventory()->addItem(Item::get(Item::DIAMOND_SWORD, 0, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Dent de loup garou'},Unbreakable:1}")));
            }
            $this->main->Courageuse->removeEffectByName("BLINDNESS");
            $this->main->Courageuse->sendMessage("Les loups garous vont faire une nouvelle victime ! Soyez discret(e), espionnez les et tapez les pour les ralentir !");
            $this->main->current = "lg";
            break;
            case 325:
            $e = \pocketmine\entity\Effect::getEffectByName("BLINDNESS");
            $e->setDuration(9999999);
            $e->setAmbient();
            $e->setVisible(false);
            $e->setAmplifier(3);
            foreach($this->main->LoupGarou as $lg) {
                $lg->getInventory()->clearAll();
            }
            foreach($this->main->getInGamePlayers() as $p) {
                $p->addEffect($e);
            }
            $this->main->broadcastMessage("Les loups garou se rendorment...");
            break;
            case 330:
            if(isset($this->main->Sorcière)) {
                $this->main->broadcastMessage("La sorcière se réveille !");
                $this->main->Sorcière->removeEffectByName("BLINDNESS");
                $r = rand(0, 40);
                if($r <= 3) {
                    if($r == 0) {
                        $this->main->potions["speed"] = true;
                        $this->main->Sorcière->sendMessage("Vous avez concocté une potion de speed !");
                    }
                    if($r == 1) {
                        $this->main->potions["slowness"] = true;
                        $this->main->Sorcière->sendMessage("Vous avez concocté une potion de lenteur !");
                    }
                    if($r == 2) {
                        $this->main->potions["regen"] = true;
                        $this->main->Sorcière->sendMessage("Vous avez concocté une potion de regeneration !");
                    }
                    if($r == 3) {
                        $this->main->potions["poison"] = true;
                        $this->main->Sorcière->sendMessage("Vous avez concocté une potion de poison !");
                    }
                }
                $this->main->Sorcière->sendMessage($this->main->killed->getName() . " a été tué cette nuit ! Souaitez vous le resuciter (boire la potion de vie), ou tuer une autre personne (la taper avec la potion de mort). Faites attention, ces potions n'ont qu'un seul usage dans la partie.");
                if($this->main->potions["life"]) {
                    $this->main->Sorcière->getInventory()->addItem(Item::get(Item::POTION, 21, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Potion de vie\\n \\n \\n \\n'},Unbreakable:1}")));
                }
                if($this->main->potions["death"]) {
                    $this->main->Sorcière->getInventory()->addItem(Item::get(Item::POTION, 23, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Potion de mort\\n \\n \\n \\n'},Unbreakable:1}")));
                }
                if($this->main->potions["speed"]) {
                    $this->main->Sorcière->getInventory()->addItem(Item::get(Item::POTION, 23, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Potion de speed\\n \\n \\n \\n'},Unbreakable:1}")));
                }
                if($this->main->potions["slowness"]) {
                    $this->main->Sorcière->getInventory()->addItem(Item::get(Item::POTION, 23, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Potion de lenteur\\n \\n \\n \\n'},Unbreakable:1}")));
                }
                if($this->main->potions["poison"]) {
                    $this->main->Sorcière->getInventory()->addItem(Item::get(Item::POTION, 23, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Potion de poison\\n \\n \\n \\n'},Unbreakable:1}")));
                }
                if($this->main->potions["regen"]) {
                    $this->main->Sorcière->getInventory()->addItem(Item::get(Item::POTION, 23, 1)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{display:{Name:'Potion de regen\\n \\n \\n \\n'},Unbreakable:1}")));
                }
                $this->main->current = "sorcière";
            } else {
                $this->turn = 395;
            }
            break;
            case 390:
            $e = \pocketmine\entity\Effect::getEffectByName("BLINDNESS");
            $e->setDuration(9999999);
            $e->setAmbient();
            $e->setVisible(false);
            $e->setAmplifier(3);
            foreach($this->main->getInGamePlayers() as $p) {
                $p->addEffect($e);
            }
            $this->main->broadcastMessage("La sorcière se rendort...");
            break;
            case 395:
            $this->main->broadcastMessage("Le village se reveille...");
            $this->main->getLevel()->setTime(23000);
            break;
            case 400:
            $this->main->getLevel()->setTime(0);
            if(isset($this->main->killed) and isset($this->main->killed2)) {
                $this->main->broadcastMessage("Cette nuit, 2 personnes sont morte. Le " . $this->getRole($this->main->killed) . " et le " . $this->getRole($this->main->killed2) . " !");
                if($this->getRole($this->main->killed2) == "Meurtrier") {
                    $this->main->broadcastMessage("Mais le meutrier, dans sa chute, a tué une autre personne.");
                    $this->main->Meurtier->sendMessage("Choisissez une persone à tuer en la tapant.");
                    $this->main->current = "meurtrier";
                } else {
                    $this->main->eliminate($this->main->killed2);
                    $this->turn = 465;
                }
                if($this->getRole($this->main->killed) == "Meurtrier") {
                    $this->main->broadcastMessage("Mais le meutrier, dans sa chute, a tué une autre personne.");
                    $this->main->Meurtier->sendMessage("Choisissez une persone à tuer en la tapant.");
                    $this->main->current = "meurtrier";
                } else {
                    $this->main->eliminate($this->main->killed);
                    $this->turn = 465;
                }
                if(!isset($m)) $this->turn = 465; else $this->main->current = "meutrier";
            } elseif(isset($this->main->killed)) {
                $this->main->broadcastMessage("Cette nuit, 1 personne est morte. Le " . $this->getRole($this->main->killed) . " !");
                if($this->getRole($this->main->killed) == "Meurtrier") {
                    $this->main->broadcastMessage("Mais le meutrier, dans sa chute, a tué une autre personne.");
                    $this->main->Meurtier->sendMessage("Choisissez une persone à tuer en la tapant.");
                    $this->main->current = "meurtrier";
                } else {
                    $this->main->eliminate($this->main->killed);
                    $this->turn = 465;
                }
            } elseif(isset($this->main->killed2)) {
                $this->main->broadcastMessage("Cette nuit, 1 personne est morte. Le " . $this->getRole($this->main->killed2) . " !");
                if($this->getRole($this->main->killed2) == "Meurtrier") {
                    $this->main->broadcastMessage("Mais le meutrier, dans sa chute, a tué une autre personne.");
                    $this->main->Meurtier->sendMessage("Choisissez une persone à tuer en la tapant.");
                    $this->main->current = "meurtrier";
                } else {
                    $this->main->eliminate($this->main->killed2);
                    $this->turn = 465;
                }
            } else {
                $this->main->broadcastMessage("Cette nuit, Personne n'est mort ! C'est un miracle !");
            }
            break;
            case 465:
            if(isset($this->main->oldMaire)){
                if($this->main->oldMaire == $this->main->maire) {
                    $this->main->maire = $this->main->getLevel()->getPlayers(rand(0, count($this->getLevel()->getPlayers())));
                    $this->main->broadcastMessage("Le nouveau maire est " . $this->main->maire->getName() . " !");
                }
            }
            $this->turn = 530; // Also to not have to rechoose the mayor one more time.
            break;
            case 530:
            $this->main->broadcastMessage("Maintenant, nous devons choisir un personne qui doit mourir. Tapez la personne que vous trouvez la plus suspecte !");
            $this->main->current = "vote";
            break;
            case 590:
            $uppestVote = 0;
            foreach($this->main->voted as $running) {
                if($running > $uppestVote) {
                    $uppestVote = $running;
                }
            }
            $runnings = [];
            foreach($this->main->voted as $name => $running) {
                if($running == $uppestVote) {
                    $runnings[] = $name;
                }
            }
            if(count($runnings) > 1) {
                $last = $runnings[count($runnings) - 1];
                unset($runnings[count($runnings) - 1]);
                $this->main->broadcastMessage(implode(", " . $runnings) . " et $last ont le même resultat ! Vous pourez tuer celui qui sera tiré au hasard sur ceux ci.");
                $this->main->mort = $this->main->getServer()->getPlayer($runnings(rand(0, count($runnings))));
                $this->main->broadcastMessage("La personne mise à mort est {$this->main->mort->getName()} ! Tuez la !");
                foreach($this->main->getLevel()->getPlayers() as $p) {
                    $p->addItem(Item::get(Item::IRON_SWORD)->setCompoundTag(\pocketmine\nbt\NBT::parseJSON("{Unbreakable:1}")));
                }
                $this->main->mort->sendMessage("Ils veulent votre mort ! Fuyez ou tuez vos enemies !");
                $this->main->current = "kill";
            }
            break;
            case 650:
            foreach($this->main->getLevel()->getPlayers() as $p) {
                $p->getInventory()->clearAll();
            }
            $this->turn = 0;
            break;
        }
        if(isset($this->speed)) {
            $e = \pocketmine\entity\Effect::getEffect(1);
            $e->setAmplifier(2);
            $e->setVisible(false);
            $e->setDuration(40);
            $this->speed->addEffect($e);
        }
        if(isset($this->slow)) {
            $e = \pocketmine\entity\Effect::getEffect(2);
            $e->setAmplifier(2);
            $e->setVisible(false);
            $e->setDuration(40);
            $this->slow->addEffect($e);
        }
        if(isset($this->poison)) {
            $e = \pocketmine\entity\Effect::getEffectByName("POISON");
            $e->setAmplifier(2);
            $e->setVisible(false);
            $e->setDuration(40);
            $this->poison->addEffect($e);
        }
        if(isset($this->regen)) {
            $e = \pocketmine\entity\Effect::getEffect(10);
            $e->setAmplifier(2);
            $e->setVisible(false);
            $e->setDuration(40);
            $this->regen->addEffect($e);
        }
        $this->turn++;
    }
}