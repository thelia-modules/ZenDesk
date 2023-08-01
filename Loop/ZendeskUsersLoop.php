<?php

namespace ZenDesk\Loop;

use IntlDateFormatter;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
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

        $manager = new ZenDeskManager();
        $zendeskUsers = $manager->getAllUsers();

        foreach ($zendeskUsers as $user)
        {
            if ($this->getEmail() === $user->email)
            {
                $tmpItems = [];
                $tmpItems[] = $user->name;
                $tmpItems[] = $user->email;
                $tmpItems[] = $user->role;
                $tmpItems[] = $this->getFormatDate(strtotime($user->created_at), $this->getLocale());
                $tmpItems[] = $this->getFormatDate(strtotime($user->updated_at), $this->getLocale());
                $tmpItems[] = $user->locale;

                $items[] = $tmpItems;
            }
        }

        return $items;
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