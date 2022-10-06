<?php

declare(strict_types=1);

namespace w1zardz\Weather;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{
    public const CLEAR = 0;
    public const LIGHT_RAIN = 1;
    public const MODERATE_RAIN = 2;
    public const HEAVY_RAIN = 3;
    public const LIGHT_THUNDER = 4;
    public const MODERATE_THUNDER = 5;
    public const HEAVY_THUNDER = 6;
    public static $instance;
    public $timer;
    public $weather;
    public $weatherobj;

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $permission = $command->getPermission();

        if (!$sender->hasPermission($permission) and !$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage("§cНет прав§r");
            return true;
        }

        if (count($args) < 1) {
            return false;
        }

        if (count($args) > 1) {
            $duration = (int)$args[1];
        } else {
            $duration = 0;
        }

        if ($args[0] === "clear") {
            $this->weatherobj->switchWeather(Main::CLEAR, $duration);
            $sender->sendMessage($this->weatherobj->weatherQuery());
            return true;
        }

        if ($args[0] === "rain") {
            $this->weatherobj->switchWeather(Main::MODERATE_RAIN, $duration);
            $sender->sendMessage($this->weatherobj->weatherQuery());
            return true;
        }

        if ($args[0] === "thunder") {
            $this->weatherobj->switchWeather(Main::MODERATE_THUNDER, $duration);
            $sender->sendMessage($this->weatherobj->weatherQuery());
            return true;
        }

        if ($args[0] === "query" or $args[0] === "status") {
            $sender->sendMessage($this->weatherobj->weatherQuery());
            return true;
        }

        return false;
    }

    protected function onEnable(): void
    {
        self::$instance = $this;

        $this->saveDefaultConfig();
        $this->weatherobj = (new Weather);

        if ($this->getConfig()->get("startclear") == true) {
            $this->weatherobj->switchWeather(Main::CLEAR, 0);
        } else {
            $this->weatherobj->switchWeather(mt_rand(Main::CLEAR, Main::HEAVY_THUNDER), 0);
        }

        $this->getScheduler()->scheduleRepeatingTask(new Schedule(), 20);
        $this->getServer()->getPluginManager()->registerEvents(new Listen(), $this);

        if ($this->getConfig()->get("console") == true) {
            $this->getServer()->getLogger()->info($this->weatherobj->weatherQuery());
        }
    }
}
