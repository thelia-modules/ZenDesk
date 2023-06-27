<?php

namespace ZenDesk\Service;

use DateTime;
use Thelia\Core\Translation\Translator;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

class RetailerTicketsService
{
    public function formatRetailerTickets(ZenDeskManager $manager, array $tickets): array
    {
        $formatted_tickets = [];
        $formatted_ticket = [];

        foreach ($tickets as $ticketsType) {
            foreach ($ticketsType as $ticket)
            {
                $createdAt = new DateTime($ticket->created_at);
                $updateAt = new DateTime($ticket->updated_at);

                $formatted_ticket["subject"] = $ticket->subject;
                $formatted_ticket["id"] = $ticket->id;

                $formatted_ticket["requester"] = $manager->getUserById($ticket->requester_id)->user->name;
                $formatted_ticket["assignee"] = $manager->getUserById($ticket->assignee_id)->user->name;

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

                $formatted_ticket["data-status"] = $ticket->status;

                if (!$this->isIdExist($formatted_tickets, $formatted_ticket["id"])){
                    $formatted_tickets[$formatted_ticket["id"]] = $formatted_ticket;
                }
            }
        }

        krsort($formatted_tickets);

        return $formatted_tickets;
    }

    private function isIdExist(array $array, int $id): bool
    {
        foreach ($array as $ticket)
        {
            if ($ticket["id"] === $id){
                return true;
            }
        }

        return false;
    }

    public function getFormattedTicketsUser(ZenDeskManager $manager, string $user): ?array
    {
        $tickets = $manager->getTicketsUser($user);

        if ($tickets !== null){
            return $this->formatRetailerTickets($manager, $tickets);
        }

        return null;
    }

    public function getOrganizationId(ZenDeskManager $manager, string $organization) :?int
    {
        $organizations = $manager->getAllOrganization();

        foreach ($organizations as $orga){
            if ($orga->name === $organization){
                return $orga->id;
            }
        }

        return null;
    }
}