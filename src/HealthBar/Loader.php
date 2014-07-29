<?php
namespace HealthBar;

use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use EssentialsPE\Loader as EssentialsPE;

class Loader extends PluginBase{
    public $essentialspe;
    private $canRemove;

    public function onEnable(){
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("healthbar", new HealthBarCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);
        $this->checkConfiguration();
        $ess = $this->getServer()->getPluginManager()->getPlugin("EssentialsPE");
        if($ess instanceof Plugin && $ess->isEnabled()){
            $this->essentialspe = new EssentialsPE();
        }
    }

    public function onDisable(){
        if($this->canRemove === true){
            foreach($this->getServer()->getOnlinePlayers() as $p){
                $this->setHealthBar($p, false);
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
            $this->setHealthBar($p, true);
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

    protected $players = [];

    public function getPlayer($player){
        $player = strtolower($player);
        $r = false;
        foreach($this->getServer()->getOnlinePlayers() as $p){
            if(strtolower($p->getName()) == $player || strtolower($p->getDisplayName()) == $player){
                $r = $p;
            }
        }
        return $r;
    }

    public function getStyle(){
        $style = strtolower($this->getConfig()->get("style"));
        switch(strtolower($style)){
            case "default":
                return $style;
                break;
        }
        return false;
    }

    public function getPosition(){
        $position = strtolower($this->getConfig()->get("position"));
        switch($position){
            case "above":
            case "under":
            case "left":
            case "right":
                return $position;
                break;
        }
        return false;
    }

    public function setStyle($style){
        $style = strtolower($style);
        switch($style){
            case "default":
                $this->getConfig()->set("style", $style);
                $this->getConfig()->save();
                break;
        }
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->updateHealthBar($p, $p->getMaxHealth(), $p->getDisplayName());
        }
        return true;
    }

    public function setPosition($position){
        $position = strtolower($position);
        switch($position){
            case "above":
            case "under":
            case "left":
            case "right":
                $this->getConfig()->set("position", $position);
                $this->getConfig()->save();
                break;
        }
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->updateHealthBar($p, $p->getMaxHealth(), $p->getDisplayName());
        }
        return true;
    }

    public function updateHealthBar(Player $player, $health = false, $name = false){
        if(!$this->isHealthBarEnabled($player)){
            return false;
        }
        $style = $this->getStyle();
        $position = $this->getPosition();
        $maxhealth = $player->getMaxHealth();
        if($health === false){
            $health = $player->getHealth();
        }elseif($name === false){
            $name = $player->getDisplayName();
        }

        if($style === false || $position === false){
            return false;
        }

        switch($style){
            case "default":
                $style = "[" . $health . "/" . $maxhealth . "]";
                break;
            /*case "retro":
                $bar = "";
                $h = $health;
                $mh = $maxhealth - $health;
                while($h >= 1 && $h % 2){
                    $bar .= $bar . "|";
                    $h--;
                    $h--;
                }
                while($mh >= 1 && $mh % 2){
                    $bar .= $bar . ":";
                    $mh--;
                    $mh--;
                }
                $style = $bar;
                break;
            case "slim":
                $bar = "";
                $h = $health;
                $mh = $maxhealth - $health;
                while($h >= 1 && $h % 2){
                    $bar .= $bar . "=";
                    $h--;
                    $h--;
                }
                while($mh >= 1 && $mh % 2){
                    $bar .= $bar . "-";
                    $mh--;
                    $mh--;
                }
                $style = $bar;
                break;*/
        }

        switch($position){
            case "above":
                $player->setNameTag($style . "\n" . $name);
                break;
            case "under":
                $player->setNameTag($name . "\n" . $style);
                break;
            case "left":
                $player->setNameTag($style . " " . $name);
                break;
            case "right":
                $player->setNameTag($name . " " . $style);
                break;
        }
        return true;
    }

    public function removeHealthBar(Player $player){
        if($this->canRemove === true){
            $player->setNameTag($player->getDisplayName());
        }
    }

    public function getHealthBar(){
        $style = $this->getStyle();
        $position = $this->getPosition();
        $maxhealth = "maxhealth";
        $health = "health";
        $name = "name";

        if($style === false || $position === false){
            return false;
        }

        switch($style){
            case "default":
                $style = "[" . $health . "/" . $maxhealth . "]";
                break;
        }

        $bar = "";
        switch($position){
            case "above":
                $bar = $style . "\n" . $name;
                break;
            case "under":
                $bar = $name . "\n" . $style;
                break;
            case "left":
                $bar = $style . " " . $name;
                break;
            case "right":
                $bar = $name . " " . $style;
                break;
        }
        return $bar;
    }

    public function isHealthBarEnabled(Player $player){
        if(!isset($this->players[$player->getName()])){
            $this->players[$player->getName()] = true;
        }
        if($this->players[$player->getName()] === false){
            return false;
        }else{
            return true;
        }
    }

    public function setHealthBar(Player $player, $value = true, $health = false){
        if(!is_bool($value)){
            return false;
        }
        $this->players[$player->getName()] = $value;
        if($value === false){
            $this->removeHealthBar($player);
        }else{
            $this->updateHealthBar($player, $health);
        }
        return true;
    }
}
