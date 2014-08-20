<?php
namespace HealthBar;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class HealthBarCommand extends Command implements PluginIdentifiableCommand{
    public $plugin;

    public function __construct(Loader $plugin){
        parent::__construct("healthbar", "Toggle HealthBar", "Usage: /healthbar <style|position|toggle>", ["hbar"]);
        $this->setPermission("healthbar.command");
        $this->plugin = $plugin;
    }

    public function getPlugin(){
        return $this->plugin;
    }

    public function execute(CommandSender $sender, $alias, array $args){
        if(!$this->testPermission($sender)){
            return false;
        }
        switch(count($args)){
            case 1:
                switch(strtolower($args[0])){
                    case "style":
                        if(!$sender->hasPermission("healthbar.command.style")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "" : "Usage: ") . "/healthbar style <desired style>");
                        break;
                    case "position":
                        if(!$sender->hasPermission("healthbar.command.position")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "" : "Usage: ") . "/healthbar position <desired position>");
                        break;
                    case "toggle":
                        if(!$sender->hasPermission("healthbar.command.toggle")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "" : "Usage: ") . "/healthbar toggle <on|off> " . ($sender instanceof Player ? "[player]" : "<player>"));
                        break;
                    default:
                        $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "" : "Usage: ") . $this->getUsage());
                        break;
                }
                break;
            case 2:
                switch(strtolower($args[0])){
                    case "style":
                        if(!$sender->hasPermission("healthbar.command.style")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        switch(strtolower($args[1])){
                            case "default":
                                $this->plugin->setStyle(strtolower($args[1]));
                                $sender->sendMessage(TextFormat::YELLOW . "[HealthBar] Updating style...");
                                break;
                            default:
                                $sender->sendMessage(TextFormat::RED . "Unknown style given, HealthBar will not be updated.");
                                break;
                        }
                        break;
                    case "position":
                        if(!$sender->hasPermission("healthbar.command.position")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        switch(strtolower($args[1])){
                            case "above":
                            case "under":
                            case "left":
                            case "right":
                                $this->plugin->setPosition(strtolower($args[1]));
                                $sender->sendMessage(TextFormat::YELLOW . "[HealthBar] Updating position...");
                                break;
                            default:
                                $sender->sendMessage(TextFormat::RED . "Unknown position given, HealthBar will not be updated.");
                                break;
                        }
                        break;
                    case "toggle":
                        if(!$sender->hasPermission("healthbar.command.toggle.use")){
                            $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                            return false;
                        }
                        if(!$sender instanceof Player){
                            $sender->sendMessage(TextFormat::RED . "Usage: /healthbar toggle <on|off> <player>");
                            return false;
                        }
                        switch(strtolower($args[1])){
                            case "on":
                                $sender->sendMessage(TextFormat::YELLOW . "Setting your HealthBar...");
                                $this->plugin->setHealthBar($sender, true);
                                break;
                            case "off":
                                $sender->sendMessage(TextFormat::YELLOW . "Removing your HealthBar...");
                                $this->plugin->setHealthBar($sender, false);
                                break;
                            default:
                                $sender->sendMessage(TextFormat::RED . "Usage: /healthbar toggle <on|off> [player]");
                                break;
                        }
                        break;
                    default:
                        $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "" : "Usage: ") . $this->getUsage());
                        break;
                }
                break;
            case 3:
                if(!$sender->hasPermission("healthbar.command.toggle.other")){
                    $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                    return false;
                }
                if(strtolower($args[0]) !== "toggle"){
                    $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
                    return false;
                }
                $player = $this->plugin->getPlayer($args[2]);
                if($player === false){
                    $sender->sendMessage(TextFormat::RED . "[Error] Player not found.");
                    return false;
                }
                switch(strtolower($args[1])){
                    case "on":
                        $sender->sendMessage(TextFormat::YELLOW . "Setting $args[2]'" . (substr($args[2], -1, 1) === "s" ? "" : "s") . " HealthBar...");
                        $player->sendMessage(TextFormat::YELLOW . "Setting your HealthBar...");
                        $this->plugin->setHealthBar($player, true);
                        break;
                    case "off":
                        $sender->sendMessage(TextFormat::YELLOW . "Removing $args[2]'" . (substr($args[2], -1, 1) === "s" ? "" : "s") . " HealthBar...");
                        $player->sendMessage(TextFormat::YELLOW . "Removing your HealthBar...");
                        $this->plugin->setHealthBar($player, false);
                        break;
                    default:
                        $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "" : "Usage: ") . "/healthbar toggle <on|off> " . ($sender instanceof Player ? "[player]" : "<player>"));
                        break;
                }
                break;
            default:
                $sender->sendMessage(TextFormat::RED . ($sender instanceof Player ? "" : "Usage: ") . $this->getUsage());
                return false;
                break;
        }
        return true;
    }
} 