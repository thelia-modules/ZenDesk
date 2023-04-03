<?php

namespace ZenDesk\Utils;

use Zendesk\API\HttpClient as ZendeskAPI;
use ZenDesk\ZenDesk;

class ZenDeskManager
{
    public function getTicketsUser(
        string $user,
        int    $page = -1,
        int    $perPage = -1): ?array
    {
        $client = new ZendeskAPI(ZenDesk::getConfigValue("zen_desk_api_subdomain"));
        $client->setAuth('basic',
            [
                'username' => ZenDesk::getConfigValue("zen_desk_api_username"),
                'token' => ZenDesk::getConfigValue("zen_desk_api_token")
            ]
        );

        // Get the customer
        $stdCustomer = $client->users()->search(array("query" => $user));

        if ($stdCustomer->users != null) {
            $customerId = $stdCustomer->users[0]->id;

            if ($page == -1 || $perPage == -1) {
                //get all customer's ticket
                $tickets = $client->users($customerId)->requests()->findAll(['sort_order' => 'desc']);

                return get_object_vars($tickets);
            }

            //get all customer's ticket with page and limit
            $tickets = $client->users($customerId)->requests()->findAll(['per_page' => $perPage, 'page' => $page, 'sort_order' => 'desc']);

            return get_object_vars($tickets);
        }
        return null;
    }
}