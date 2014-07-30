<?php
namespace HealthBar\OtherEvents;

use EssentialsPE\Events\PlayerNickChangeEvent;
use HealthBar\Loader;
use pocketmine\event\Listener;

class EssentialsPEEvents implements Listener{
    /** @var \HealthBar\Loader  */
    public $plugin;

    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerNickChangeEvent $event
     */
    public function onNickChange(PlayerNickChangeEvent $event){
        $player = $event->getPlayer();
        $nick = $event->getNewNick();

        $bar = $this->plugin->getHealthBar();
        $bar = str_replace("maxhealth", $player->getMaxHealth(), $bar);
        $bar = str_replace("health", $player->getHealth(), $bar);
        $bar = str_replace("name", $nick, $bar);

        $event->setNameTag($bar);
    }
} 