<?php

namespace ZenDesk\Service;

use EasyDataTableManager\Service\DataTable\BaseDataTable;
use Exception;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\Security\SecurityContext;
use Thelia\Core\Template\ParserInterface;
use Thelia\Core\Translation\Translator;
use Thelia\Log\Tlog;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

class ZenDeskTicketsDataTable extends BaseDataTable
{
    protected $request;

    public function __construct(
        protected ZenDeskManager  $manager,
        protected SecurityContext $securityContext,
        protected ZendeskService  $service,
        RequestStack              $requestStack,
        EventDispatcherInterface  $dispatcher,
        ParserInterface           $parser,
    )
    {
        parent::__construct($requestStack, $dispatcher, $parser);
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @throws Exception
     */
    public function buildResponseData($type): Response
    {
        try {
            $tickets = $this->manager->getTicketsByUser($this->securityContext->getCustomerUser()->getEmail());

            $sumTickets = $this->manager->getSumTicketsByUser(
                $this->securityContext->getCustomerUser()->getEmail()
            );

            $json = [];

            if ($tickets !== null) {
                $sortedTickets = $this->service->sortOrderTickets(
                    $this->request->get('order')[0],
                    $this->getDefineColumnsDefinition(true)[(int)$this->request->get('order')[0]['column']],
                    $tickets
                );

                $json = [
                    "draw" => (int)$this->request->get('draw'),
                    "recordsTotal" => $sumTickets,
                    "recordsFiltered" => $sumTickets,
                    "data" => [],
                    "tickets" => count($tickets)
                ];
                foreach ($sortedTickets as $ticket) {
                    $json['data'][] = $this->service->jsonFormat($ticket);
                }
            }

        } catch (Exception $ex) {
            Tlog::getInstance()->addError('Zenddesk datatable error : ' . $ex->getMessage());
            $json = [
                "draw" => (int)$this->request->get('draw'),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "tickets" => 0
            ];
        }

        return new JsonResponse($json);
    }

    /**
     * @throws \SmartyException
     */
    public function getFiltersTemplate($params = []): string
    {
        return $this->parser->render('datatable/render/zendesk.render.datatable.tickets.js.html', []);
    }

    /**
     * @throws \SmartyException
     */
    public function getRendersTemplate($params = []): string
    {
        return $this->parser->render('datatable/render/zendesk.render.datatable.tickets.js.html', []);
    }

    public function getDefineColumnsDefinition($type): array
    {
        $i = -1;

        $ticketType = ZenDesk::getConfigValue("zen_desk_ticket_type");
        $hiddenColumn = ZenDesk::getConfigValue("zen_desk_hide_column");

        $definitions = [];

        $definitions[] =
            [
                'name' => 'id',
                'targets' => ++$i,
                'className' => "text-center id",
                'title' => Translator::getInstance()->trans('ID', [], ZenDesk::DOMAIN_NAME)
            ];

        $definitions[] =
            [
                'name' => 'subject',
                'targets' => ++$i,
                'className' => "text-center subject",
                'title' => Translator::getInstance()->trans('Subject', [], ZenDesk::DOMAIN_NAME)
            ];

        if ($ticketType === "assigned" || $ticketType === "all" && $hiddenColumn !== "requested hide") {
            $definitions[] =
                [
                    'name' => 'requester_id',
                    'targets' => ++$i,
                    'className' => "text-center",
                    'title' => Translator::getInstance()->trans('Requester', [], ZenDesk::DOMAIN_NAME),
                ];
        }

        if ($ticketType === "requested" || $ticketType === "all" && $hiddenColumn !== "assigned hide") {
            $definitions[] =
                [
                    'name' => 'assignee_id',
                    'targets' => ++$i,
                    'className' => "text-center",
                    'title' => Translator::getInstance()->trans('Assignee', [], ZenDesk::DOMAIN_NAME)
                ];
        }

        $definitions[] =
            [
                'name' => 'created_at',
                'targets' => ++$i,
                'className' => "text-center createdat",
                'title' => Translator::getInstance()->trans('Created At', [], ZenDesk::DOMAIN_NAME),
            ];

        $definitions[] =
            [
                'name' => 'updated_at',
                'targets' => ++$i,
                'className' => "text-center updatedat",
                'title' => Translator::getInstance()->trans('Update At', [], ZenDesk::DOMAIN_NAME),
            ];

        $definitions[] =
            [
                'name' => 'status',
                'targets' => ++$i,
                'className' => "text-center status",
                'render' => "renderStatus",
                'title' => Translator::getInstance()->trans('Status', [], ZenDesk::DOMAIN_NAME),
            ];

        $definitions[] =
            [
                'name' => 'comments',
                'targets' => ++$i,
                'className' => "text-center comments",
                'render' => "renderCommentsFunction",
                'title' => Translator::getInstance()->trans('Actions', [], ZenDesk::DOMAIN_NAME),
            ];

        return $definitions;
    }

    public function handleSearch(ModelCriteria $query): void
    {
        // TODO: Implement handleSearch() method.
    }
}