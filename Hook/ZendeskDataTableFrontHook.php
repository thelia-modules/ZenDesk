<?php

namespace ZenDesk\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class ZendeskDataTableFrontHook extends BaseHook
{
    public function renderDataTableTools(HookRenderEvent $event)
    {
        $event->add(
            $this->render('datatable/render/zendesk.render.datatable.tickets.js.html', [])
        );
    }
}