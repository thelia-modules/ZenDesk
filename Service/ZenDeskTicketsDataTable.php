<?php

namespace ZenDesk\Service;

use DateTime;
use EasyDataTableManager\Service\DataTable\BaseDataTable;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\Security\SecurityContext;
use Thelia\Core\Template\ParserInterface;
use Thelia\Core\Translation\Translator;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

class ZenDeskTicketsDataTable extends BaseDataTable
{
    protected $request;

    public function __construct(
        protected ZenDeskManager           $manager,
        protected RetailerTicketsService   $service,
        protected SecurityContext          $securityContext,
        RequestStack             $requestStack,
        EventDispatcherInterface $dispatcher,
        ParserInterface          $parser,
    )
    {
        parent::__construct($requestStack, $dispatcher, $parser);
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @return bool true if retailer has tickets
     */
    public function hasTickets(): bool
    {
        return 0 < $this->manager->getTicketsUser(
                $this->securityContext->getCustomerUser()->getEmail(),
            );
    }

    public function buildResponseData($type): Response
    {
        $zenDeskUrl = "https://" . ZenDesk::getConfigValue("zen_desk_api_subdomain") . ".zendesk.com/agent/tickets/";

        $tickets = $this->manager->getTicketsUser(
            $this->securityContext->getCustomerUser()->getEmail(),
        );

        $json = [];

        if ($tickets !== null) {
            $formattedTickets = $this->service->formatRetailerTickets($this->manager, $tickets);

            $sortedTickets = $this->sortOrder($formattedTickets);

            $json = [
                "draw" => (int)$this->request->get('draw'),
                "recordsTotal" => count($sortedTickets),
                "recordsFiltered" => count($sortedTickets),
                "data" => [],
            ];

            foreach ($sortedTickets as $ticket)
            {
                $createdAt = new DateTime($ticket["created_at"]);
                $updateAt = new DateTime($ticket["update_at"]);

                $json['data'][] =
                    [
                        $ticket["id"],
                        $ticket["subject"],
                        $ticket["requester"],
                        $ticket["assignee"],
                        $createdAt->format('d/m/Y'),
                        $updateAt->format('d/m/Y'),
                        [
                            "status" => $ticket["status"],
                            "data_status" => $ticket["data-status"]
                        ],
                        [
                            'id' => $ticket["id"],
                        ]
                    ]
                ;
            }
        }

        return new JsonResponse($json);
    }

    public function getFiltersTemplate($params = []): string
    {
        return $this->parser->render('datatable/render/zendesk.render.datatable.tickets.js.html', []);
    }

    public function getRendersTemplate($params = []): string
    {
        return $this->parser->render('datatable/render/zendesk.render.datatable.tickets.js.html', []);
    }

    public function sortOrder(array $tickets): array
    {
        $columnDefinition = $this->getDefineColumnsDefinition(true)[(int)$this->request->get('order')[0]['column']];
        $sort = (string)$this->request->get('order')[0]['dir'] === 'asc' ? Criteria::ASC : Criteria::DESC;

        if (!(int)$this->request->get('order')[0]['column'])
        {
           //order by update date desc + status asc
            usort($tickets, function($a, $b) use ($columnDefinition)
            {
                $resDate = -strcmp($a["update_at"], $b["update_at"]);
                $resStatus = strcmp($a["data-status"], $b["data-status"]);

                return $resDate + $resStatus;
            });

            return $tickets;
        }

        if (strcmp($sort, "ASC"))
        {
            usort($tickets, function($a, $b) use ($columnDefinition)
            {
                return -strcmp($a[$columnDefinition["name"]], $b[$columnDefinition["name"]]);
            });
        }

        if (strcmp($sort, "DESC"))
        {
            usort($tickets, function($a, $b) use ($columnDefinition)
            {
                return strcmp($a[$columnDefinition["name"]], $b[$columnDefinition["name"]]);
            });
        }

        return $tickets;
    }

    public function getDefineColumnsDefinition($type): array
    {
        $i = -1;

        $definitions = [
            [
                'name' => 'id',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('ID', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'subject',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Subject', [], ZenDesk::DOMAIN_NAME),
            ],

            [
                'name' => 'requester',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Requester', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'assignee',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Assignee', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'created_at',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Created At', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'update_at',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Update At', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'status',
                'targets' => ++$i,
                'className' => "text-center",
                'render' => "renderStatus",
                'title' => Translator::getInstance()->trans('Status', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'comments',
                'targets' => ++$i,
                'className' => "text-center",
                'render' => "renderCommentsFunction",
                'title' => Translator::getInstance()->trans('Actions', [], ZenDesk::DOMAIN_NAME),
            ],
        ];

        return $definitions;
    }

    public function handleSearch(ModelCriteria $query): void
    {
        // TODO: Implement handleSearch() method.
    }
}