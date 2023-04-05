<?php

namespace ZenDesk\Loop;

use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\CustomerQuery;
use ZenDesk\Utils\ZenDeskManager;

class ZendeskCustomersLoop extends BaseLoop implements PropelSearchLoopInterface
{
    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $customer) {
            // Create a new result
            $loopResultRow = new LoopResultRow($customer);

            // Assign variable that will be accessible in smarty by $PROFILE for example
            $loopResultRow->set('ID', $customer->getId())
                ->set('EMAIL', $customer->getEmail())
            ;
            $this->addOutputFields($loopResultRow, $customer);

            // Add the result to loop result list
            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }

    public function buildModelCriteria()
    {
        $manager = new ZenDeskManager();
        $zendeskUsers = $manager->getAllUsers();

        $search = CustomerQuery::create();

        foreach ($zendeskUsers as $user)
        {
            $search->filterByEmail($user->email);
            $search->_or();
        }

        return $search;
    }

    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('id')
        );
    }
}