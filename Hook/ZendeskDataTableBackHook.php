<?php

namespace ZenDesk\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class ZendeskDataTableBackHook extends BaseHook
{
    public function renderDataTableTools(HookRenderEvent $event): void
    {
        $event->add($this->render('datatable/render/zendesk.render.datatable.users.js.html'));
    }
}