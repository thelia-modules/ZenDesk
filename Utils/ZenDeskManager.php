<?php

namespace ZendDesk\Utils;

use Zendesk\API\HttpClient as ZendeskAPI;

class ZenDeskManager
{
    public function getTicketsUser(string $subdomain, string $username, string $token, string $user)
    {
        $client = new ZendeskAPI($subdomain);
        $client->setAuth('basic', ['username' => $username, 'token' => $token]);

        // Get the customer
        $stdCustomer = $client->users()->search(array("query" => $user));

        if ($stdCustomer->users != null) {
            $customerId = $stdCustomer->users[0]->id;

            //get all customer's ticket
            $tickets = $client->users($customerId)->requests()->findAll();

            return get_object_vars($tickets);
        }
        return null;
    }
}