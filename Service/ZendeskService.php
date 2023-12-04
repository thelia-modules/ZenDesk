<?php

namespace ZenDesk\Service;

use DateTime;
use Exception;
use IntlDateFormatter;
use Propel\Runtime\ActiveQuery\Criteria;
use stdClass;
use Thelia\Core\Security\SecurityContext;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Customer;
use Thelia\Model\CustomerQuery;
use Thelia\Tools\URL;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\API\Exceptions\ResponseException;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

class ZendeskService
{
    public function __construct(
        protected ZenDeskManager  $manager,
        protected SecurityContext $securityContext
    )
    {
    }

    public function sortOrderTickets(
        array $order,
        array $columnDefinition,
        array $arrayTickets
    ): array
    {
        $tickets = $this->formatTickets($arrayTickets);

        $ticketsNew = [];
        $ticketsOpen = [];
        $ticketsPending = [];
        $ticketsHold = [];
        $ticketsClosed = [];

        $sort = $order['dir'];

        // order by UpdatedDate and status
        if (!(int)$order['column'] && $sort == "") {
            foreach ($tickets as $ticket) {
                if ($ticket->status === "new") {
                    $ticketsNew[] = $ticket;
                }

                if ($ticket->status === "open") {
                    $ticketsOpen[] = $ticket;
                }

                if ($ticket->status === "pending") {
                    $ticketsPending[] = $ticket;
                }

                if ($ticket->status === "hold") {
                    $ticketsHold[] = $ticket;
                }

                if ($ticket->status === "closed" ||
                    $ticket->status === "solved") {
                    $ticketsClosed[] = $ticket;
                }
            }

            return array_merge(
                $this->sortByUpdatedAt($ticketsNew, $columnDefinition),
                $this->sortByUpdatedAt($ticketsOpen, $columnDefinition),
                $this->sortByUpdatedAt($ticketsPending, $columnDefinition),
                $this->sortByUpdatedAt($ticketsHold, $columnDefinition),
                $this->sortByUpdatedAt($ticketsClosed, $columnDefinition)
            );
        }

        $sort = $sort === 'asc' ? Criteria::ASC : Criteria::DESC;

        //order by ID
        if (!(int)$order['column']) {
            return $this->sortById($tickets, $columnDefinition, $sort);
        }

        return $this->sortByNameColumn($tickets, $columnDefinition, $sort);
    }

    private function sortByUpdatedAt(array $tickets, array $columnDefinition): array
    {
        usort($tickets, function ($a, $b) use ($columnDefinition) {
            return strcmp($b->updated_at, $a->updated_at);
        });

        return $tickets;
    }

    private function sortByNameColumn(array $items, array $columnDefinition, $sort): array
    {
        if ($sort === "ASC") {
            usort($items, function ($a, $b) use ($columnDefinition) {
                $index = $columnDefinition["name"];
                return strcmp($b->$index, $a->$index);
            });
        }

        if ($sort === "DESC") {
            usort($items, function ($a, $b) use ($columnDefinition) {
                $index = $columnDefinition["name"];
                return strcmp($a->$index, $b->$index);
            });
        }

        return $items;
    }

    private function sortById(array $items, array $columnDefinition, $sort): array
    {
        if ($sort === "ASC") {
            usort($items, function ($a, $b) use ($columnDefinition) {
                $index = $columnDefinition["name"];
                return $b->$index <=> $a->$index;
            });
        }

        if ($sort === "DESC") {
            usort($items, function ($a, $b) use ($columnDefinition) {
                $index = $columnDefinition["name"];
                return $a->$index <=> $b->$index;
            });
        }

        return $items;
    }

    public function sortOrderUsers(
        array $order,
        array $columnDefinition,
        array $users
    ): array
    {
        $sort = $order['dir'];

        $sort = $sort === 'asc' ? Criteria::ASC : Criteria::DESC;

        //order by ID
        if (!(int)$order['column']) {
            return $this->sortById($users, $columnDefinition, $sort);
        }

        return $this->sortByNameColumn($users, $columnDefinition, $sort);
    }

    /**
     * @throws Exception
     */
    public function jsonFormat(stdClass $ticket): array
    {
        $createdAt = new DateTime($ticket->created_at);
        $updateAt = new DateTime($ticket->updated_at);

        $ticketType = ZenDesk::getConfigValue("zen_desk_ticket_type");
        $hiddenColumn = ZenDesk::getConfigValue("zen_desk_hide_column");

        if ($ticketType === "requested" || ($ticketType === "all" && $hiddenColumn === "requested hide")) {
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

        if ($ticketType === "assigned" || ($ticketType === "all" && $hiddenColumn === "assigned hide")) {
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

    public function jsonUsersFormat(stdClass $users): array
    {
        $url = URL::getInstance()->absoluteUrl("/admin/customer/update?customer_id=" . $users->id);
        return
            [
                [
                    'href' => $url,
                    'name' => $users->ref,
                ],
                $users->name,
                $users->email,
                $users->role,
                $users->created_at,
                $users->updated_at,
                $users->locale
            ];
    }

    public function formatTickets(array $tickets): array
    {
        $formatted_tickets = [];

        foreach ($tickets as $ticketType) {
            foreach ($ticketType as $ticket) {
                $formatted_tickets[] = $ticket;
            }
        }

        return $formatted_tickets;
    }

    public function translateStatus(string $status): ?string
    {
        if ($status === "new") {
            return Translator::getInstance()->trans('new', [], ZenDesk::DOMAIN_NAME);
        }
        if ($status === "open") {
            return Translator::getInstance()->trans('open', [], ZenDesk::DOMAIN_NAME);
        }
        if ($status === "pending") {
            return Translator::getInstance()->trans('pending', [], ZenDesk::DOMAIN_NAME);
        }
        if ($status === "hold") {
            return Translator::getInstance()->trans('hold', [], ZenDesk::DOMAIN_NAME);
        }
        if ($status === "solved") {
            return Translator::getInstance()->trans('solved', [], ZenDesk::DOMAIN_NAME);
        }
        if ($status === "closed") {
            return Translator::getInstance()->trans('closed', [], ZenDesk::DOMAIN_NAME);
        }

        return null;
    }

    /**
     * @return bool true if retailer has tickets
     * @throws AuthException
     * @throws ResponseException
     */
    public function hasTickets(): bool
    {
        try {
            return 0 < $this->manager->getSumTicketsByUser(
                    $this->securityContext->getCustomerUser()->getEmail(),
                );
        } catch (Exception) {
            return false;
        }
    }

    public function formatZendeskUsersByEmail(array $zendeskUsers, string $locale): array
    {
        $items = [];

        foreach ($zendeskUsers as $users) {
            foreach ($users as $user) {
                if ($customer = $this->getCustomerByEmail($user->email)) {
                    $items[] = $this->setUsersAsObject($user, $customer, $locale);
                }
            }
        }

        return $items;
    }

    public function filterByCustomer(array $zendeskUsers, string $locale): array
    {
        $users = [];

        foreach ($zendeskUsers as $user) {
            if (!$customer = $this->getCustomerByEmail($user->email)) {
                continue;
            }

            $users[] = $this->setUsersAsObject($user, $customer, $locale);
        }

        return $users;
    }

    public function setUsersAsObject(stdClass $user, ?Customer $customer, string $locale): stdClass
    {
        $obj = new stdClass();

        if (null === $customer) {
            $obj->id = "#";
            $obj->ref = "";
        }

        if (null !== $customer) {
            $obj->id = $customer->getId();
            $obj->ref = $customer->getRef();
        }

        $obj->name = $user->name;
        $obj->email = $user->email;
        $obj->role = $user->role;
        $obj->created_at = $this->getFormatDate(strtotime($user->created_at), $locale);
        $obj->updated_at = $this->getFormatDate(strtotime($user->updated_at), $locale);
        $obj->locale = $user->locale;

        return $obj;
    }

    private function getCustomerByEmail(?string $email): ?Customer
    {
        if ($email === null) {
            return null;
        }

        return CustomerQuery::create()->findOneByEmail($email);
    }

    private function getFormatDate($datetime, string $locale): bool|string
    {
        $fmt = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE
        );

        $fmt->setPattern('dd MMMM YYYY à H:mm');

        return $fmt->format($datetime);
    }
}