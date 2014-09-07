<?php
namespace HealthBar;

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
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $this->plugin->updateHealthBar($player);
    }

    /**
     * @param EntityRegainHealthEvent $event
     */
    public function onRegainHealth(EntityRegainHealthEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
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
        if($entity instanceof Player){
            $gamemode = $entity->getServer()->getGamemodeFromString($entity->getGamemode());
            if($gamemode === 1 or $gamemode === 3){
                $event->setCancelled(true);
            }else{
                $health = $entity->getHealth() - $event->getFinalDamage();
                $this->plugin->updateHealthBar($entity, $health);
            }
        }
    }
} 
