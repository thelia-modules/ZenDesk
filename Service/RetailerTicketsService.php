<?php

namespace ZenDesk\Service;

use DateTime;

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
            $formatted_ticket["status"] = $ticket->status;

            $formatted_tickets[] = $formatted_ticket;
        }

        return $formatted_tickets;
    }
}