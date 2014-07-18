<?php
namespace HealthBar;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase{
    private $canRemove;

    public function onEnable(){
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("healthbar", new HealthBarCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);
        $this->checkConfiguration();
    }

    public function onDisable(){
        if($this->canRemove === true){
            foreach($this->getServer()->getOnlinePlayers() as $p){
                $this->removeHealthBar($p);
            }
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

    public function getPlayer($player){
        $r = "";
        foreach($this->getServer()->getOnlinePlayers() as $p){
            if(strtolower($p->getDisplayName()) == strtolower($player) || strtolower($p->getName()) == strtolower($player)){
                $r = $this->getServer()->getPlayerExact($p->getName());
            }
        }
        if($r == ""){
            return false;
        }else{
            return $r;
        }
    }

    public function getStyle(){
        $style = $this->getConfig()->get("style");
        if($style == "default"){
            return "default";
        }elseif($style == "retro"){
            return "retro";
        }elseif($style == "slim"){
            return "slim";
        }else{
            return false;
        }
    }

    public function getPosition(){
        $position = $this->getConfig()->get("position");
        if($position == "above" || $position == "under" || $position == "left" || $position == "right"){
            return $position;
        }else{
            return false;
        }
    }

    public function setStyle($style){
        switch($style){
            case "default":
                $this->getConfig()->set("style", $style);
                break;
        }
        $this->getConfig()->save();
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->updateHealthBar($p);
        }
        return true;
    }

    public function setPosition($position){
        switch($position){
            case "above":
            case "under":
            case "left":
            case "right":
                $this->getConfig()->set("position", $position);
                break;
        }
        $this->getConfig()->save();
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->updateHealthBar($p);
        }
        return true;
    }

    public function updateHealthBar(Player $player, $health = false){
        $style = $this->getStyle();
        $position = $this->getPosition();
        $maxhealth = $player->getMaxHealth();
        if($health === false){
            $health = $player->getHealth();
        }

        if($style === false || $position === false){
            return false;
        }

        switch($style){
            case "default":
                $style = "[" . $health . "/" . $maxhealth . "]";
                break;
            case "retro":
                $bar = "";
                $h = $health;
                $nh = $maxhealth - $health;
                while($nh >= 1){
                    $bar = $bar . ":";
                    $nh--;
                }
                while($h >= 1){
                    $bar = $bar . "|";
                    $h--;
                }
                $style = $bar;
                break;
            case "slim":
                $bar = "";
                $h = $health;
                $nh = $maxhealth - $health;
                while($nh >= 1){
                    $bar = $bar . "-";
                    $nh--;
                }
                while($h >= 1){
                    $bar = $bar . "=";
                    $h--;
                }
                $style = $bar;
                break;
        }

        switch($position){
            case "above":
                $player->setNameTag($style . "\n" . $player->getDisplayName());
                break;
            case "under":
                $player->setNameTag($player->getDisplayName() . "\n" . $style);
                break;
            case "left":
                $player->setNameTag($style . " " . $player->getDisplayName());
                break;
            case "right":
                $player->setNameTag($player->getDisplayName() . " " . $style);
                break;
        }
        return true;
    }

    public function removeHealthBar(Player $player){
        if($this->canRemove === true){
            $player->setNameTag($player->getDisplayName());
        }
    }
}
