<?php

namespace ZenDesk\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use ZenDesk\ZenDesk;

class ConfigurationHook extends BaseHook
{
    public function onModuleConfiguration(HookRenderEvent $event)
    {
        $event->add($this->render("module_configuration.html", [
            'url' => ZenDesk::getConfigValue('zen_desk_api_subdomain'),
            'key' => ZenDesk::getConfigValue('zen_desk_api_username'),
            'login' => ZenDesk::getConfigValue('zen_desk_api_token'),
        ]));
    }

    public static function getSubscribedHooks()
    {
        return [
            "module.configuration" => [
                [
                    "type" => "back",
                    "method" => "onModuleConfiguration"
                ],
            ]
        ];
    }
}