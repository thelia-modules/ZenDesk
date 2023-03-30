<?php

namespace ZenDesk\Service;

class RetailerTicketsService
{
    public function formatRetailerTickets(array $tickets): array
    {
        $formatted_tickets = [];

        foreach ($tickets["requests"] as $ticket) {

            $createdAt = new \DateTime($ticket->created_at);
            $updateAt = new \DateTime($ticket->updated_at);

            $formatted_tickets[]["subject"] = $ticket->subject;
            $formatted_tickets[]["id"] = $ticket->id;
            $formatted_tickets[]["createdAt"] = $createdAt->format('d/m/Y');
            $formatted_tickets[]["updateAt"] = $updateAt->format('d/m/Y');
            $formatted_tickets[]["status"] = $ticket->status;
        }

        return $formatted_tickets;
    }
}