<?php

namespace ZenDesk\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Front\BaseFrontController;
use ZenDesk\Utils\ZenDeskManager;

#[Route('/zendesk', name: 'zendesk_front_')]
class FrontController extends BaseFrontController
{
    #[Route('/tickets/{id}/comments', name: 'tickets_comments')]
    public function getCommentsByTicketsId(ZenDeskManager $manager, $id)
    {
        $comments = $manager->getCommentTicket($id);

        $formattedComment = [];

        foreach ($comments["comments"] as $comment){
            $formattedComment[] = $comment->html_body;
        }

        return $this->render("comments", [
            "comments" => $formattedComment,
            "ticketId" => $id
         ]);
    }
}