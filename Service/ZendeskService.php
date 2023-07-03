<?php

namespace ZenDesk\Service;

use DateTime;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Security\SecurityContext;
use Thelia\Core\Translation\Translator;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

class ZendeskService
{
    public function __construct(
        protected ZenDeskManager $manager,
        protected SecurityContext $securityContext
    ) {}

    public function sortOrder(
        array $order,
        array $columnDefinition,
        array $arrayTickets
    ): array
    {
        $tickets = $this->formatTickets($arrayTickets);

        $ticketsNew = [];
        $ticketsOpen = [];
        $ticketsPending = [];
        $ticketsClosed = [];

        $sort = $order['dir'];

        // order by UpdatedDate and status
        if (!(int)$order['column'] && $sort == "")
        {
            foreach ($tickets as $ticket)
            {
                if ($ticket->status === "new")
                {
                    $ticketsNew[] = $ticket;
                }

                if ($ticket->status === "open")
                {
                    $ticketsOpen[] = $ticket;
                }

                if ($ticket->status === "pending")
                {
                    $ticketsPending[] = $ticket;
                }

                if ($ticket->status === "closed" ||
                    $ticket->status === "solved")
                {
                    $ticketsClosed[] = $ticket;
                }
            }

            return array_merge(
                $this->sortByUpdatedAt($ticketsNew, $columnDefinition),
                $this->sortByUpdatedAt($ticketsOpen, $columnDefinition),
                $this->sortByUpdatedAt($ticketsPending, $columnDefinition),
                $this->sortByUpdatedAt($ticketsClosed, $columnDefinition)
            );
        }

        $sort = $sort === 'asc' ? Criteria::ASC : Criteria::DESC;

        //order by ID
        if (!(int)$order['column'])
        {
            return $this->sortById($tickets, $columnDefinition, $sort);
        }

        return $this->sortByNameColumn($tickets, $columnDefinition, $sort);
    }

    private function sortByUpdatedAt(array $tickets, array $columnDefinition): array
    {
        usort($tickets, function($a, $b) use ($columnDefinition)
        {
            return strcmp($b->updated_at, $a->updated_at);
        });

        return $tickets;
    }

    private function sortByNameColumn(array $tickets, array $columnDefinition, $sort): array
    {
        if ($sort === "ASC")
        {
            usort($tickets, function ($a, $b) use ($columnDefinition) {
                $index = $columnDefinition["name"];
                return strcmp($b->$index, $a->$index);
            });
        }

        if ($sort === "DESC")
        {
            usort($tickets, function ($a, $b) use ($columnDefinition) {
                $index = $columnDefinition["name"];
                return strcmp($a->$index, $b->$index);
            });
        }

        return $tickets;
    }

    private function sortById(array $tickets, array $columnDefinition, $sort): array
    {
        if ($sort === "ASC")
        {
            usort($tickets, function ($a, $b) use ($columnDefinition) {
                $index = $columnDefinition["name"];
                return $b->$index <=> $a->$index;
            });
        }

        if ($sort === "DESC")
        {
            usort($tickets, function ($a, $b) use ($columnDefinition) {
                $index = $columnDefinition["name"];
                return $a->$index <=> $b->$index;
            });
        }

        return $tickets;
    }

    /**
     * @throws \Exception
     */
    public function jsonFormat(\stdClass $ticket): array
    {
        $createdAt = new DateTime($ticket->created_at);
        $updateAt = new DateTime($ticket->updated_at);

        if (ZenDesk::getConfigValue("zen_desk_ticket_type") === "requested")
        {
            return
                [
                    $ticket->id,
                    $ticket->subject,
                    $this->manager->getUserById($ticket->assignee_id)->user->name,
                    $createdAt->format('d/m/Y'),
                    $updateAt->format('d/m/Y'),
                    [
                        "status" => $this->translateStatus($ticket->status),
                        "data_status" => $ticket->status
                    ],
                    [
                        'id' => $ticket->id,
                    ]
                ];
        }

        if (ZenDesk::getConfigValue("zen_desk_ticket_type") === "assigned")
        {
            return
                [
                    $ticket->id,
                    $ticket->subject,
                    $this->manager->getUserById($ticket->requester_id)->user->name,
                    $createdAt->format('d/m/Y'),
                    $updateAt->format('d/m/Y'),
                    [
                        "status" => $this->translateStatus($ticket->status),
                        "data_status" => $ticket->status
                    ],
                    [
                        'id' => $ticket->id,
                    ]
                ];
        }

        return
            [
                $ticket->id,
                $ticket->subject,
                $this->manager->getUserById($ticket->requester_id)->user->name,
                $this->manager->getUserById($ticket->assignee_id)->user->name,
                $createdAt->format('d/m/Y'),
                $updateAt->format('d/m/Y'),
                [
                    "status" => $this->translateStatus($ticket->status),
                    "data_status" => $ticket->status
                ],
                [
                    'id' => $ticket->id,
                ]
            ];
    }

    public function formatTickets(array $tickets): array
    {
        $formatted_tickets = [];

        foreach ($tickets as $ticketType)
        {
            foreach ($ticketType as $ticket)
            {
                $formatted_tickets[] = $ticket;
            }
        }

        return $formatted_tickets;
    }

    public function translateStatus(string $status): ?string
    {
        if ($status === "new"){
            return Translator::getInstance()->trans('new', [], ZenDesk::DOMAIN_NAME);
        }
        if ($status === "open"){
            return Translator::getInstance()->trans('open', [], ZenDesk::DOMAIN_NAME);
        }
        if ($status === "pending"){
            return Translator::getInstance()->trans('pending', [], ZenDesk::DOMAIN_NAME);
        }
        if ($status === "solved"){
            return Translator::getInstance()->trans('solved', [], ZenDesk::DOMAIN_NAME);
        }
        if ($status === "closed"){
            return Translator::getInstance()->trans('closed', [], ZenDesk::DOMAIN_NAME);
        }

        return null;
    }

    /**
     * @return bool true if retailer has tickets
     */
    public function hasTickets(): bool
    {
        return 0 < $this->manager->getSumTicketsByUser(
                $this->securityContext->getCustomerUser()->getEmail(),
            );
    }
}