<?php

namespace ZendDesk\Hook;

use HookAdminHome\Hook\AdminHook;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use ZendDesk\ZendDesk;

class ConfigurationHook extends AdminHook
{
    public function onModuleConfiguration(HookRenderEvent $event)
    {
        $event->add($this->render("module_configuration.html", [
            'url' => ZendDesk::getConfigValue('zen_desk_api_subdomain'),
            'key' => ZendDesk::getConfigValue('zen_desk_api_username'),
            'login' => ZendDesk::getConfigValue('zen_desk_api_token'),
        ]));
    }
}