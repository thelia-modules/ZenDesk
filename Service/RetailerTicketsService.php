<?php

namespace ZenDesk\Service;

use DateTime;
use Thelia\Core\Translation\Translator;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

class RetailerTicketsService
{
    public function formatRetailerTickets(array $tickets): array
    {
        $formatted_tickets = [];
        $formatted_ticket = [];

        foreach ($tickets["requests"] as $ticket) {

            $createdAt = new DateTime($ticket->created_at);
            $updateAt = new DateTime($ticket->updated_at);

            $formatted_ticket["subject"] = $ticket->subject;
            $formatted_ticket["id"] = $ticket->id;
            $formatted_ticket["createdAt"] = $createdAt->format('d/m/Y');
            $formatted_ticket["updateAt"] = $updateAt->format('d/m/Y');

            if ($ticket->status === "new"){
                $formatted_ticket["status"] = Translator::getInstance()->trans('new', [], ZenDesk::DOMAIN_NAME);
            }
            if ($ticket->status === "open"){
                $formatted_ticket["status"] = Translator::getInstance()->trans('open', [], ZenDesk::DOMAIN_NAME);
            }
            if ($ticket->status === "pending"){
                $formatted_ticket["status"] = Translator::getInstance()->trans('pending', [], ZenDesk::DOMAIN_NAME);
            }
            if ($ticket->status === "solved"){
                $formatted_ticket["status"] = Translator::getInstance()->trans('solved', [], ZenDesk::DOMAIN_NAME);
            }

            $formatted_tickets[] = $formatted_ticket;
        }

        return $formatted_tickets;
    }

    public function getFormattedTicketsUser(ZenDeskManager $manager, string $user): ?array
    {
        $tickets = $manager->getTicketsUser($user);

        if ($tickets !== null){
            return $this->formatRetailerTickets($tickets);
        }

        return null;
    }
}