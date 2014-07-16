<?php
namespace HealthBar;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;

class EventHandler implements Listener{
    /** @var \HealthBar\Loader  */
    public $plugin;

    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event){
        $this->plugin->updateHealthBar($event->getPlayer());
    }

    /**
     * @param EntityRegainHealthEvent $event
     */
    public function onRegainHealth(EntityRegainHealthEvent $event){
        $entity = $event->getEntity();
        $health = $event->getAmount();
        if($entity instanceof Player){
            $this->plugin->updateHealthBar($entity, $health);
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onHealthLose(EntityDamageEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
            $health = $entity->getHealth() - $event->getFinalDamage();
            $this->plugin->updateHealthBar($entity, $health);
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     */
    public function onAttack(EntityDamageByEntityEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
            $health = $entity->getHealth() - $event->getFinalDamage();
            $this->plugin->updateHealthBar($entity, $health);
        }
    }
} 