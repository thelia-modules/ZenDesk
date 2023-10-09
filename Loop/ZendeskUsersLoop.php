<?php

namespace ZenDesk\Loop;

use IntlDateFormatter;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\Customer;
use Thelia\Model\CustomerQuery;
use ZenDesk\Utils\ZenDeskManager;

class ZendeskUsersLoop extends BaseLoop implements ArraySearchLoopInterface
{
    public function parseResults(LoopResult $loopResult): LoopResult
    {
        foreach ($loopResult->getResultDataCollection() as $item) {

            $loopResultRow = new LoopResultRow();

            $loopResultRow->set("ID", $item[0]);
            $loopResultRow->set("NAME", $item[1]);
            $loopResultRow->set("EMAIL", $item[2]);
            $loopResultRow->set("ROLE", $item[3]);
            $loopResultRow->set("CREATED_AT", $item[4]);
            $loopResultRow->set("UPDATE_AT", $item[5]);
            $loopResultRow->set("LOCALE", $item[6]);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;

    }

    public function buildArray(): array
    {
        $items = [];

        $manager = new ZenDeskManager();
        $zendeskUsers = $manager->getAllUsers();

        foreach ($zendeskUsers as $usersArray)
        {
            foreach ($usersArray as $user)
            {
                if ($customer = $this->getCustomerByEmail($user->email))
                {
                    $items[] = [
                        $customer->getId(),
                        $user->name,
                        $user->email,
                        $user->role,
                        $this->getFormatDate(strtotime($user->created_at), $this->getLocale()),
                        $this->getFormatDate(strtotime($user->updated_at), $this->getLocale()),
                        $user->locale
                    ];
                }
            }
        }

        sort($items);

        return $items;
    }

    public function getCustomerByEmail($email): ?Customer
    {
        if ($email === null)
        {
            return null;
        }

        return CustomerQuery::create()->findOneByEmail($email);
    }

    private function getFormatDate($datetime, $locale): bool|string
    {
        $fmt = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE
        );

        $fmt->setPattern('dd MMMM YYYY à H:mm');

        return $fmt->format($datetime);
    }

    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createAnyTypeArgument("email"),
            Argument::createAnyTypeArgument("locale")
        );
    }
}