<?php
namespace HealthBar;

use EssentialsPE\Events\PlayerNickChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\Server;

class EventHandler implements Listener{
    /** @var \HealthBar\Loader  */
    public $plugin;

    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param EntityRegainHealthEvent $event
     */
    public function onRegainHealth(EntityRegainHealthEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player && !$event->isCancelled()){
            $health = $entity->getHealth() + $event->getAmount();
            if($health > $entity->getMaxHealth()){
                $health = $entity->getMaxHealth();
            }
            $this->plugin->updateHealthBar($entity, $health);
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onHealthLose(EntityDamageEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player && !$event->isCancelled()){
            if(Server::getGamemodeString($entity->getGamemode()) === "SPECTATOR" || Server::getGamemodeString($entity->getGamemode()) ===  "CREATIVE"){
                $event->setCancelled(true);
            }else{
                $health = $entity->getHealth() - $event->getFinalDamage();
                $this->plugin->updateHealthBar($entity, $health);
            }
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     */
    public function onAttack(EntityDamageByEntityEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player && !$event->isCancelled()){
            if(Server::getGamemodeString($entity->getGamemode()) === "SPECTATOR" || Server::getGamemodeString($entity->getGamemode()) ===  "CREATIVE"){
                $event->setCancelled(true);
            }else{
                $health = $entity->getHealth() - $event->getFinalDamage();
                $this->plugin->updateHealthBar($entity, $health);
            }
        }
    }

    /**
     * @param PlayerNickChangeEvent $event
     */
    public function onNickChange(PlayerNickChangeEvent $event){
        $player = $event->getPlayer();
        $nick = $event->getNewNick();
        $nametag = $event->getNameTag();

        $bar = $this->plugin->getHealthBar();
        $bar = str_replace("maxhealth", $player->getMaxHealth(), $bar);
        $bar = str_replace("health", $player->getHealth(), $bar);
        $bar = str_replace("name", $nick, $bar);

        $event->setNameTag($bar);
    }
} 