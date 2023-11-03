<?php

namespace ZenDesk\Service;

use EasyDataTableManager\Service\DataTable\BaseDataTable;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use SmartyException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\Security\SecurityContext;
use Thelia\Core\Template\ParserInterface;
use Thelia\Core\Translation\Translator;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\API\Exceptions\ResponseException;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

class ZendeskUsersDataTable extends BaseDataTable
{
    protected $request;

    public function __construct(
        protected ZenDeskManager    $manager,
        protected SecurityContext   $securityContext,
        protected ZendeskService    $service,
        RequestStack                $requestStack,
        EventDispatcherInterface    $dispatcher,
        ParserInterface             $parser,
    )
    {
        parent::__construct($requestStack, $dispatcher, $parser);
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @throws ResponseException
     * @throws AuthException
     */
    public function buildResponseData($type): Response
    {
        $locale = $this->request->getSession()->get('thelia.current.lang')->getLocale();

        //no pagination
        /*
        $zendeskUsers = $this->manager->getAllUsers();
        $users = $this->service->formatZendeskUsersByEmail($zendeskUsers, $locale);
        $countUsers = count($users);
        */
        //----

        //pagination

        $nbUsersPerPages = 25;
        $start = $this->request->request->get("start");
        $page = $start / $nbUsersPerPages;

        $zendeskUsers = $this->manager->getAllUsersByPage($page, $nbUsersPerPages);

        $users = $this->service->formatZendeskUsersForPagination($zendeskUsers, $locale);

        $countUsers = $this->manager->countAllUsers();
        
        //----

        $json = [];

        if ($users !== []) {

            $sortedUsers = $this->service->sortOrderUsers(
                $this->request->get('order')[0],
                $this->getDefineColumnsDefinition(true)[(int)$this->request->get('order')[0]['column']],
                $users
            );

            $json = [
                "draw" => (int)$this->request->get('draw'),
                "recordsTotal" => $countUsers,
                "recordsFiltered" => $countUsers,
                "data" => [],
                "users" => $countUsers
            ];

            foreach ($sortedUsers as $user)
            {
                $json['data'][] = $this->service->jsonUsersFormat($user);
            }
        }

        return new JsonResponse($json);
    }

    public function getDefineColumnsDefinition($type): array
    {
        return $this->defineZendeskUsersColumnsDefinition();
    }

    public function handleSearch(ModelCriteria $query): void
    {
        // TODO: Implement handleSearch() method.
    }

    /**
     * @throws SmartyException
     */
    public function getFiltersTemplate($params = []): string
    {
        return $this->parser->render('datatable/render/zendesk.render.datatable.users.js.html', []);
    }

    /**
     * @throws SmartyException
     */
    public function getRendersTemplate($params = []): string
    {
        return $this->parser->render('datatable/render/zendesk.render.datatable.users.js.html', []);
    }

    private function defineZendeskUsersColumnsDefinition(): array
    {
        $i = -1;

        return [
            [
                'name' => 'id',
                'targets' => ++$i,
                'className' => "text-center",
                'render' => "renderRefFunction",
                'title' => Translator::getInstance()->trans('Customer code', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'name',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Name', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'email',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Email', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'role',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Role', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'created_at',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Created_At', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'updated_at',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Updated_At', [], ZenDesk::DOMAIN_NAME),
            ],
            [
                'name' => 'locale',
                'targets' => ++$i,
                'className' => "text-center",
                'title' => Translator::getInstance()->trans('Locale', [], ZenDesk::DOMAIN_NAME),
            ]
        ];
    }
}