<?php

namespace ZenDesk\Service;

use EasyDataTableManager\Service\DataTable\BaseDataTable;
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
            $formatedTickets = $this->service->formatRetailerTickets($tickets);

            $json = [
                "draw" => (int)$this->request->get('draw'),
                "recordsTotal" => count($tickets["requests"]),
                "recordsFiltered" => count($tickets["requests"]),
                "data" => [],
            ];

            foreach ($formatedTickets as $formatedTicket) {
                $json['data'][] = [
                    $formatedTicket["id"],
                    $formatedTicket["subject"],
                    $formatedTicket["createdAt"],
                    $formatedTicket["updateAt"],
                    $formatedTicket["status"],
                    [
                        'id' => $formatedTicket["id"],
                    ],
                ];
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