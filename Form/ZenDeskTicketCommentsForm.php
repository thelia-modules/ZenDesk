<?php

namespace ZenDesk\Form;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use ZenDesk\ZenDesk;

class ZenDeskTicketCommentsForm extends BaseForm
{

    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                "comment_reply",
                TextType::class,
                [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('comment_reply', [], ZenDesk::DOMAIN_NAME)
                ])
            ->add(
                "attachments",
                FileType::class,
                [
                    'required' => false,
                    'label' => Translator::getInstance()->trans('attachments', [], ZenDesk::DOMAIN_NAME),
                    'multiple' => true
                ]
            )
        ;
    }
}