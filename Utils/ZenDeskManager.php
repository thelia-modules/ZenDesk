<?php

namespace ZenDesk\Utils;

use Zendesk\API\HttpClient;
use Zendesk\API\HttpClient as ZendeskAPI;
use ZenDesk\ZenDesk;

class ZenDeskManager
{
    public function getUserByEmail(string $mail, HttpClient $client = null): ?\stdClass
    {
        if ($client === null){
            $client = $this->authZendeskAdmin();
        }

        return  $client->users()->search(array("query" => $mail));
    }

    public function getUserById(int $id, HttpClient $client = null): ?\stdClass
    {
        if ($client === null){
            $client = $this->authZendeskAdmin();
        }

        return  $client->users()->find($id);
    }

    public function getTicketsByUser(string $user): ?array
    {
        $tickets = [];
        $option = ZenDesk::getConfigValue("zen_desk_ticket_type");

        if (0 === strcmp($option, "assigned")){
            $tickets["assigned"] = $this->getTicketsAssignedByUser($user);
        }

        if (0 === strcmp($option, "requested")){
            $tickets["requested"] = $this->getTicketsRequestedByUser($user);
        }

        if (0 === strcmp($option, "all")){
            $tickets["requested"] = $this->getTicketsRequestedByUser($user);
            $tickets["assigned"] = $this->getTicketsAssignedByUser($user);
        }

        return $tickets;
    }

    public function getSumTicketsByUser(string $user): int
    {
        $option = ZenDesk::getConfigValue("zen_desk_ticket_type");

        if (0 === strcmp($option, "assigned")){
            return $this->getSumTicketsAssignedByUser($user);
        }

        if (0 === strcmp($option, "requested")){
            return $this->getSumTicketsRequestedByUser($user);
        }

        return
            $this->getSumTicketsRequestedByUser($user) +
            $this->getSumTicketsAssignedByUser($user)
            ;
    }

    private function getTicketsRequestedByUser(string $user,): ?array
    {
        $client = $this->authZendeskAdmin();

        $stdCustomer = $this->getUserByEmail($user, $client);

        if ($stdCustomer->users != null) {
            $customerId = $stdCustomer->users[0]->id;

            return $client->users($customerId)->tickets()->requested()->tickets;
        }
        return null;
    }

    private function getTicketsAssignedByUser(string $user): ?array
    {
        $client = $this->authZendeskAdmin();

        $stdCustomer = $this->getUserByEmail($user, $client);

        if ($stdCustomer->users != null) {
            $customerId = $stdCustomer->users[0]->id;

            return $client->users($customerId)->tickets()->assigned()->tickets;
        }
        return null;
    }

    public function getSumTicketsRequestedByUser(string $user): int
    {
        $client = $this->authZendeskAdmin();

        $stdCustomer = $this->getUserByEmail($user, $client);

        if ($stdCustomer->users != null) {
            $customerId = $stdCustomer->users[0]->id;

            return $client->users($customerId)->tickets()->requested()->count;
        }

        return  0;
    }

    public function getSumTicketsAssignedByUser(string $user): int
    {
        $client = $this->authZendeskAdmin();

        $stdCustomer = $this->getUserByEmail($user, $client);

        if ($stdCustomer->users != null) {
            $customerId = $stdCustomer->users[0]->id;

            return $client->users($customerId)->tickets()->assigned()->count;
        }

        return  0;
    }

    public function getAllUsers()
    {
        $client = $this->authZendeskAdmin();

        return $client->users()->findAll()->users;
    }

    private function authZendeskAdmin(): ZendeskAPI
    {
        $client = new ZendeskAPI(ZenDesk::getConfigValue("zen_desk_api_subdomain"));
        $client->setAuth('basic',
            [
                'username' => ZenDesk::getConfigValue("zen_desk_api_username"),
                'token' => ZenDesk::getConfigValue("zen_desk_api_token")
            ]
        );

        return $client;
    }

    public function getCommentTicket(int $id): array
    {
        $client = $this->authZendeskAdmin();

        return get_object_vars($client->tickets($id)->comments()->findAll());
    }

    public function getCommentAuthor($author_id): array
    {
        $client = $this->authZendeskAdmin();

        return get_object_vars($client->users()->find($author_id));
    }

    public function getTicket($id): array
    {
        $client = $this->authZendeskAdmin();

        return get_object_vars($client->tickets($id)->find());
    }

    public function createTicket(array $params): void
    {
        $client = $this->authZendeskAdmin();

        $client->tickets()->create($params);
    }

    /**
     * Update is used to update parameters like status
     * or adding a new comment for a ticket
     *
     * @param array $params
     * @param $id
     * @return void
     */
    public function updateTicket(array $params, $id = null): void
    {
        $client = $this->authZendeskAdmin();

        $client->tickets()->update($id, $params);
    }

    public function uploadFile(array $upload): ?\stdClass
    {
        $client = $this->authZendeskAdmin();

        return $client->attachments()->upload($upload);
    }

    public function getAllGroup()
    {
        $client = $this->authZendeskAdmin();

        return $client->groups()->findAll()->groups;
    }

    public function getAllOrganization()
    {
        $client = $this->authZendeskAdmin();

        return $client->organizations()->findAll()->organizations;
    }

    public function getOrganizationId(string $organization) :?int
    {
        $organizations = $this->getAllOrganization();

        foreach ($organizations as $oneOrganization){
            if ($oneOrganization->name === $organization){
                return $oneOrganization->id;
            }
        }

        return null;
    }
}