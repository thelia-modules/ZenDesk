<?php

namespace ZenDesk\Loop;

use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use ZenDesk\Utils\ZenDeskManager;

class ZendeskUsersLoop extends BaseLoop implements ArraySearchLoopInterface
{
    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $item) {

            $loopResultRow = new LoopResultRow();

            $loopResultRow->set("NAME", $item[0]);
            $loopResultRow->set("EMAIL", $item[1]);
            $loopResultRow->set("ROLE", $item[2]);
            $loopResultRow->set("CREATED_AT", $item[3]);
            $loopResultRow->set("UPDATE_AT", $item[4]);
            $loopResultRow->set("LOCALE", $item[5]);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;

    }

    public function buildArray()
    {
        $items = [];

//$search = CustomerQuery::create();

//$search->joinProcityCustomerFamily()->filterById(1)->find();

        $manager = new ZenDeskManager();
        $zendeskUsers = $manager->getAllUsers();

        foreach ($zendeskUsers as $user)
        {
            $tmpItems = [];
            $tmpItems[] = $user->name;
            $tmpItems[] = $user->email;
            $tmpItems[] = $user->role;
            $tmpItems[] = $user->created_at;
            $tmpItems[] = $user->updated_at;
            $tmpItems[] = $user->locale;

            $items[] = $tmpItems;
        }

        return $items;

    }

    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
        );
    }
}