<?php

namespace ZenDesk\Controller;

use IntlDateFormatter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\SecurityContext;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Translation\Translator;
use ZenDesk\Form\ZenDeskTicketCommentsForm;
use ZenDesk\Form\ZenDeskTicketForm;
use ZenDesk\Service\RetailerTicketsService;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

#[Route('/zendesk', name: 'zendesk_front_')]
class FrontController extends BaseFrontController
{
    #[Route('/tickets', name: 'create_tickets')]
    public function createNewTickets(
        SecurityContext $securityContext,
        ZenDeskManager $manager,
        RetailerTicketsService $service,
        ParserContext $parserContext
    ) {
        $form = $this->createForm(ZenDeskTicketForm::getName());

        try {
            $data = $this->validateForm($form)->getData();

            $requester = $manager->getUserByEmail($securityContext->getCustomerUser()->getEmail());
            $assignee = $manager->getUserByEmail($data["assignee"]);

            if (!$requester){
                throw new \Exception(Translator::getInstance()->trans(
                    "Requester not Found",
                    [],
                    ZenDesk::DOMAIN_NAME
                ));
            }

            if (!$assignee){
                throw new \Exception(Translator::getInstance()->trans(
                    "Assignee not Found",
                    [],
                    ZenDesk::DOMAIN_NAME
                ));
            }

            $organization_id = $service->getOrganizationId($manager, $data["organization"]);

            if (!$organization_id){
                throw new \Exception(Translator::getInstance()->trans(
                    "Organization not Found",
                    [],
                    ZenDesk::DOMAIN_NAME
                ));
            }

            $params = [
                "subject" => $data["subject"],
                "comment" => [
                    "body" => $data["description"],
                    "author_id" => $requester->users[0]->id,
                    'uploads'   => $this->uploadFileGetToken($manager, $data["attachments"])
                ],
                "type" => $data["type"],
                "status" => "new",
                "priority" => $data["priority"],
                "tags" => explode(",", $data["tags"]),
                "is_public" => true,
                "requester_id" => $requester->users[0]->id,
                "submitter_id" => $requester->users[0]->id,
                "assignee_id" => $assignee->users[0]->id,
                "organization_id" => $organization_id,
            ];

            $manager->createTicket($params);

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
        }

        $form->setErrorMessage($error_message);
        $parserContext
            ->addForm($form)
            ->setGeneralError($error_message);

        return $this->generateErrorRedirect($form);
    }

    #[Route('/tickets/{id}/comments', name: 'tickets_comments_get', methods: 'GET')]
    public function getCommentsByTicketsId(RequestStack $requestStack, ZenDeskManager $manager, $id): Response
    {
        $locale = $requestStack->getCurrentRequest()->getSession()->getLang()->getLocale();
        $ticket = $manager->getTicket($id);
        $ticketName = $ticket["ticket"]->subject;
        $comments = $manager->getCommentTicket($id);

        $formattedComment = [];

        foreach ($comments["comments"] as $keyComment => $comment){
            $author = $manager->getCommentAuthor($comment->author_id)["user"];

            $formattedComment[$keyComment]["author_name"] = $author->name;
            $formattedComment[$keyComment]["author_email"] = $author->email;
            $formattedComment[$keyComment]["author_picture_url"] = $author->photo?->mapped_content_url;
            $formattedComment[$keyComment]["created_at"] = $this->getDateZendesk(strtotime($comment->created_at), $locale);
            $formattedComment[$keyComment]["created_at_str"] = $this->getDateFormatZendesk(strtotime($comment->created_at));
            $formattedComment[$keyComment]["body"] = $comment->html_body;
            $formattedComment[$keyComment]["attachments"] = [];

            if ($comment->attachments) {
                foreach ($comment->attachments as $keyAttachment => $attachment){
                    $formattedComment[$keyComment]["attachments"][$keyAttachment]["file_name"] = $attachment->file_name;
                    $formattedComment[$keyComment]["attachments"][$keyAttachment]["content_url"] = $attachment->content_url;
                }
            }
        }

        return $this->render("comments", [
            "comments" => $formattedComment,
            "ticketId" => $id,
            "ticketName" => $ticketName
         ]);
    }

    #[Route('/tickets/{id}/comments', name: 'tickets_comments_post', methods: 'POST')]
    public function createNewCommentTicket(
        SecurityContext $securityContext,
        ZenDeskManager $manager,
        ParserContext $parserContext,
        $id
    ) {
        $form = $this->createForm(ZenDeskTicketCommentsForm::getName());

        try {
            $data = $this->validateForm($form)->getData();

            $requester = $manager->getUserByEmail($securityContext->getCustomerUser()->getEmail());

            $params = [
                "comment" => [
                    "body" => $data["comment_reply"],
                    "author_id" => $requester->users[0]->id,
                    'uploads'   => $this->uploadFileGetToken($manager, $data["attachments"])
                ]
            ];

            $manager->createComment($params, $id);

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
        }
        $form->setErrorMessage($error_message);

        $parserContext
            ->addForm($form)
            ->setGeneralError($error_message);

        return $this->generateErrorRedirect($form);
    }


    /**
     * Upload Files in Zendesk and get their token
     *
     * @param ZenDeskManager $manager
     * @param array $attachments the files transmitted in form
     * @return array of the token uploaded file
     */
    private function uploadFileGetToken(
        ZenDeskManager $manager,
        array $attachments
    ): array
    {
        $uploads = [];

        if ($attachments)
        {
            foreach ($attachments as $attachment)
            {
                $upload = [
                    "file" => $attachment->getpathname(),
                    "type" => $attachment->getClientMimeType(),
                    "name" => $attachment->getClientOriginalName()
                ];

                $attachment = $manager->uploadFile($upload);

                $uploads[] = $attachment->upload->token;
            }
        }

        return $uploads;
    }

    private function getDateZendesk(string $dateTime, string $locale) :string
    {
        $date = date('Y-m-d H:i:s', $dateTime);
        $cal = \IntlCalendar::fromDateTime($date, $locale);

        return IntlDateFormatter::formatObject(
            $cal,
            Translator::getInstance()->trans("'The'", [],ZenDesk::DOMAIN_NAME) .
            " d MMMM yyyy " .
            Translator::getInstance()->trans("'at'", [],ZenDesk::DOMAIN_NAME)." HH'h'mm",
            $locale
        );
    }

    private function getDateFormatZendesk(string $dateTime) :string
    {
        $auj = date("Y-m-d H:i:s");
        $secs = strtotime($auj) - $dateTime;
        $days = round($secs / 86400);

        if ($days < 1){
            return Translator::getInstance()->trans("today", [], ZenDesk::DOMAIN_NAME);
        }
        if ($days < 2){
            return Translator::getInstance()->trans("yesterday", [], ZenDesk::DOMAIN_NAME);
        }
        if ($days < 7){
            return Translator::getInstance()->trans("%days days ago", ["%days" => $days], ZenDesk::DOMAIN_NAME);
        }
        if ($days < 31){
            return Translator::getInstance()->trans("%weeks weeks ago", ["%weeks" => round($days/7)], ZenDesk::DOMAIN_NAME);
        }
        if ($days < 365){
            return Translator::getInstance()->trans("%months months ago", ["%months" => round($days/31)], ZenDesk::DOMAIN_NAME);
        }

        return Translator::getInstance()->trans("%years years ago", ["%years" => round($days/365)], ZenDesk::DOMAIN_NAME);
    }
}