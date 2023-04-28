<?php

namespace ZenDesk\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Translation\Translator;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

#[Route('/zendesk', name: 'zendesk_front_')]
class FrontController extends BaseFrontController
{
    #[Route('/tickets/{id}/comments', name: 'tickets_comments')]
    public function getCommentsByTicketsId(ZenDeskManager $manager, $id): Response
    {
        $ticket = $manager->getTicket($id);
        $ticketName = $ticket["ticket"]->subject;
        $comments = $manager->getCommentTicket($id);

        $formattedComment = [];

        foreach ($comments["comments"] as $keyComment => $comment){
            $author = $manager->getCommentAuthor($comment->author_id)["user"];

            $formattedComment[$keyComment]["author_name"] = $author->name;
            $formattedComment[$keyComment]["author_email"] = $author->email;
            $formattedComment[$keyComment]["author_picture_url"] = $author->photo->mapped_content_url;
            $formattedComment[$keyComment]["created_at"] = date('d F Y à H\hi', strtotime($comment->created_at));
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
            return Translator::getInstance()->trans("%weeks weeks ago", ["%weeks" => $days/7], ZenDesk::DOMAIN_NAME);
        }
        if ($days < 365){
            return Translator::getInstance()->trans("%months months ago", ["%months" => $days/31], ZenDesk::DOMAIN_NAME);
        }

        return Translator::getInstance()->trans("%years years ago", ["%years" => $days/365], ZenDesk::DOMAIN_NAME);
    }
}