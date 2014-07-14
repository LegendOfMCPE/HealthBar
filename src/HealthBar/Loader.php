<?php
namespace HealthBar;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase implements Listener{
    private $canRemove;

    public function onEnable(){
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->checkConfiguration();
    }

    public function onDisable(){
        if($this->canRemove === true){
            foreach($this->getServer()->getOnlinePlayers() as $p){
                $this->removeHealthBar($p);
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event){
        $this->updateHealthBar($event->getPlayer());
    }

    public function onRegainHealth(EntityRegainHealthEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
            $this->updateHealthBar($entity);
        }
    }

    public function onHealthLose(EntityDamageEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
            $this->updateHealthBar($entity);
        }
    }

    public function onAttack(EntityDamageByEntityEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
            $this->updateHealthBar($entity);
        }
    }

    private function checkConfiguration(){
        if(!$this->getStyle()){
            $this->getLogger()->info(TextFormat::YELLOW . "[HealthBar] " . TextFormat::RED . "Unknown style given, HealthBar will be disabled!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            $this->setEnabled(false);
            return false;
        }elseif(!$this->getPosition()){
            $this->getLogger()->info(TextFormat::YELLOW . "[HealthBar] " . TextFormat::RED . "Unknown position given, HealthBar will be disabled!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            $this->setEnabled(false);
            return false;
        }else{
           $this->enableHealthBar();
            return true;
        }
    }

    private function enableHealthBar(){
        $this->canRemove = true;
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->updateHealthBar($p);
        }
    }

    /*
     *  .----------------.  .----------------.  .----------------.
     * | .--------------. || .--------------. || .--------------. |
     * | |      __      | || |   ______     | || |     _____    | |
     * | |     /  \     | || |  |_   __ \   | || |    |_   _|   | |
     * | |    / /\ \    | || |    | |__) |  | || |      | |     | |
     * | |   / ____ \   | || |    |  ___/   | || |      | |     | |
     * | | _/ /    \ \_ | || |   _| |_      | || |     _| |_    | |
     * | ||____|  |____|| || |  |_____|     | || |    |_____|   | |
     * | |              | || |              | || |              | |
     * | '--------------' || '--------------' || '--------------' |
     *  '----------------'  '----------------'  '----------------'
     *
     */

    public function getStyle(){
        $style = $this->getConfig()->get("style");
        if($style == "default"){
            return "default";
        }else{
            return false;
        }
    }

    public function getPosition(){
        $position = $this->getConfig()->get("position");
        if($position == "above"){
            return "above";
        }elseif($position == "under"){
            return "under";
        }else{
            return false;
        }
    }

    public function updateHealthBar(Player $player){
        $style = $this->getStyle();
        $position = $this->getPosition();

        if($style == "default"){
            $style = $player->getHealth() . "/" . $player->getMaxHealth();
        }

        if($position == "above"){
            $player->setNameTag($style . "\n" . $player->getDisplayName());
        }elseif($position == "under"){
            $player->setNameTag($player->getDisplayName() . "\n" . $style);
        }
        return true;
    }

    public function removeHealthBar(Player $player){
        if($this->canRemove === true){
            $player->setNameTag($player->getDisplayName());
        }
    }
}
