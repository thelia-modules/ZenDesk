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
        $tmp = [];

        foreach ($comments["comments"] as $comment){
            $tmp["author_name"] = $manager->getCommentAuthor($comment->author_id)["user"]->name;
            $tmp["author_email"] = $manager->getCommentAuthor($comment->author_id)["user"]->email;
            $tmp["created_at"] = date('d F Y à H\hi', strtotime($comment->created_at));
            $tmp["body"] = $comment->html_body;

            $formattedComment[] = $tmp;
        }

        return $this->render("comments", [
            "comments" => $formattedComment,
            "ticketId" => $id
         ]);
    }
}