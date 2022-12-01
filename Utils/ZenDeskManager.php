<?php

namespace ZendDesk\Utils;

use Zendesk\API\HttpClient as ZendeskAPI;

class ZenDeskManager
{
    public function getAllTickets(string $subdomain, string $username, string $token)
    {
        /*
        $subdomain = "openstudio-test";
        $username  = "tdasilva@openstudio.fr"; // replace this with your registered email
        $token     = "vNZuS4ydLHhOrUGXxAQZRqG8lGVICG6LtxWckGoF"; // replace this with your token
        //$secret    = "25c3339baf146e670b421f1a24cb3ef1bc68019259c9e534de5419163c213795";
        */

        $client = new ZendeskAPI($subdomain);
        $client->setAuth('basic', ['username' => $username, 'token' => $token]);

        // Get all tickets
        $tickets = $client->tickets()->findAll();

        return get_object_vars($tickets);
    }

    public function getTicketsUser(string $subdomain, string $username, string $token, string $user)
    {
        $client = new ZendeskAPI($subdomain);
        $client->setAuth('basic', ['username' => $username, 'token' => $token]);

        // Get the customer
        $stdCustomer = $client->users()->search(array("query" => $user));

        if ($stdCustomer->users === null) {
            $customerId = $stdCustomer->users[0]->id;

            //get all customer's ticket
            $tickets = $client->users($customerId)->requests()->findAll();

            return get_object_vars($tickets);
        }
        return null;
    }
}