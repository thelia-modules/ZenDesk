<?php

namespace ZenDesk\Service;

class RetailerTicketsService
{
    public function formatRetailerTickets(array $tickets): array
    {
        $formatted_tickets = [];
        $index = 0;

        foreach ($tickets["requests"] as $ticket) {

            $createdAt = new \DateTime($ticket->created_at);
            $updateAt = new \DateTime($ticket->updated_at);

            $formatted_tickets[$index]["subject"] = $ticket->subject;
            $formatted_tickets[$index]["id"] = $ticket->id;
            $formatted_tickets[$index]["createdAt"] = $createdAt->format('d/m/Y');
            $formatted_tickets[$index]["updateAt"] = $updateAt->format('d/m/Y');
            $formatted_tickets[$index]["status"] = $ticket->status;
            $index += 1;
        }

        return $formatted_tickets;
    }
}