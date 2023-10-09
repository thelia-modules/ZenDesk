<?php

namespace ZenDesk\Utils;

use Zendesk\API\Exceptions\ApiResponseException;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\API\Exceptions\CustomException;
use Zendesk\API\Exceptions\MissingParametersException;
use Zendesk\API\Exceptions\ResponseException;
use Zendesk\API\HttpClient;
use Zendesk\API\HttpClient as ZendeskAPI;
use ZenDesk\ZenDesk;

class ZenDeskManager
{
    /**
     * @throws AuthException
     */
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


    /**
     * @throws ResponseException
     * @throws AuthException
     */
    public function getAllUsers(): array
    {
        $client = $this->authZendeskAdmin();

        $page = 1;
        $allUsers = [];

        while ([] !== $users = $client->users()->findAll(['per_page' => 100, 'page' => $page, 'sort_order' => "desc"])->users)
        {
            $allUsers[] = $users;
            $page += 1;
        }

        return $allUsers;
    }

    /**
     * @throws ResponseException
     * @throws AuthException
     */
    public function getUserByEmail(string $mail, HttpClient $client = null): ?\stdClass
    {
        if ($client === null){
            $client = $this->authZendeskAdmin();
        }

        return  $client->users()->search(array("query" => $mail));
    }

    /**
     * @throws MissingParametersException
     * @throws AuthException
     */
    public function getUserById(int $id, HttpClient $client = null): ?\stdClass
    {
        if ($client === null){
            $client = $this->authZendeskAdmin();
        }

        return  $client->users()->find($id);
    }


    /**
     * @throws ResponseException
     * @throws AuthException
     */
    public function getTicketsByUser(string $user): ?array
    {
        $tickets = [];
        $option = ZenDesk::getConfigValue("zen_desk_ticket_type");

        if ($option === "assigned")
        {
            $tickets["assigned"] = $this->getTicketsAssignedByUser($user);
        }

        if ($option === "requested")
        {
            $tickets["requested"] = $this->getTicketsRequestedByUser($user);
        }

        if ($option === "all")
        {
            $tickets["requested"] = $this->getTicketsRequestedByUser($user);
            $tickets["assigned"] = $this->getTicketsAssignedByUser($user);
        }

        return $tickets;
    }

    /**
     * @throws ResponseException
     * @throws AuthException
     */
    private function getTicketsRequestedByUser(string $user): ?array
    {
        $client = $this->authZendeskAdmin();

        $stdCustomer = $this->getUserByEmail($user, $client);

        if ($stdCustomer->users != null) {
            $customerId = $stdCustomer->users[0]->id;

            return $client->users($customerId)->tickets()->requested()->tickets;
        }
        return null;
    }

    /**
     * @throws ResponseException
     * @throws AuthException
     */
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


    /**
     * @throws ResponseException
     * @throws AuthException
     */
    public function getSumTicketsByUser(string $user): int
    {
        $option = ZenDesk::getConfigValue("zen_desk_ticket_type");

        if ($option === "assigned")
        {
            return $this->getSumTicketsAssignedByUser($user);
        }

        if ($option === "requested")
        {
            return $this->getSumTicketsRequestedByUser($user);
        }

        return
            $this->getSumTicketsRequestedByUser($user) +
            $this->getSumTicketsAssignedByUser($user)
            ;
    }

    /**
     * @throws ResponseException
     * @throws AuthException
     */
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

    /**
     * @throws ResponseException
     * @throws AuthException
     */
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


    /**
     * @throws MissingParametersException
     * @throws AuthException
     */
    public function getCommentTicket(int $id): array
    {
        $client = $this->authZendeskAdmin();

        return get_object_vars($client->tickets($id)->comments()->findAll());
    }

    /**
     * @throws MissingParametersException
     * @throws AuthException
     */
    public function getCommentAuthor($author_id): array
    {
        $client = $this->authZendeskAdmin();

        return get_object_vars($client->users()->find($author_id));
    }


    /**
     * @throws MissingParametersException
     * @throws AuthException
     */
    public function getTicket($id): array
    {
        $client = $this->authZendeskAdmin();

        return get_object_vars($client->tickets($id)->find());
    }

    /**
     * @throws ApiResponseException
     * @throws ResponseException
     * @throws AuthException
     */
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
     * @throws AuthException
     */
    public function updateTicket(array $params, $id = null): void
    {
        $client = $this->authZendeskAdmin();

        $client->tickets()->update($id, $params);
    }

    /**
     * Upload a file to Zendesk
     * You need to upload a file before set it to your ticket
     * Return the upload param of a comment (cf Zendesk docs)
     *
     * @param array $upload
     * @return \stdClass|null
     * @throws CustomException
     * @throws MissingParametersException|AuthException
     */
    public function uploadFile(array $upload): ?\stdClass
    {
        $client = $this->authZendeskAdmin();

        return $client->attachments()->upload($upload);
    }


    /**
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function getAllGroup()
    {
        $client = $this->authZendeskAdmin();

        return $client->groups()->findAll()->groups;
    }

    /**
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function getAllOrganization()
    {
        $client = $this->authZendeskAdmin();

        return $client->organizations()->findAll()->organizations;
    }

    /**
     * @throws ApiResponseException
     * @throws AuthException
     */
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