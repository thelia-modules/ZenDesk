<?php

namespace ZenDesk\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use ZenDesk\ZenDesk;

class ZenDeskTicketForm extends BaseForm
{
    protected function buildForm() :void
    {
        $this->formBuilder
            ->add("subject",
                TextType::class,
                [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('subject', [], ZenDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => Translator::getInstance()->trans('subject of the ticket', [], ZenDesk::DOMAIN_NAME)
                    ]
                ]
            )
            ->add("description",
                TextType::class,
                [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('description', [], ZenDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => Translator::getInstance()->trans('description of your issue', [], ZenDesk::DOMAIN_NAME)
                    ]
                ]
            )
            ->add("type",
                ChoiceType::class,
                [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('type', [], ZenDesk::DOMAIN_NAME),
                    'choices'  => [
                        'Question' => "question",
                        'Incident' => "incident",
                        'Problem' => "problem",
                        'Task' => "task",
                    ],
                    'label_attr' => [
                        'help' => Translator::getInstance()->trans('type of your issue', [], ZenDesk::DOMAIN_NAME)
                    ]
                ]
            )
            ->add("priority",
                ChoiceType::class,
                [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('priority', [], ZenDesk::DOMAIN_NAME),
                    'choices'  => [
                        'Low' => "low",
                        'Normal' => "normal",
                        'High' => "high",
                        'Urgent' => "urgent",
                    ],
                    'label_attr' => [
                        'help' => Translator::getInstance()->trans('priority of your issue', [], ZenDesk::DOMAIN_NAME)
                    ]
                ]
            )
            ->add("tags",
                TextType::class,
                [
                    'required' => false,
                    'label' => Translator::getInstance()->trans('tags', [], ZenDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => Translator::getInstance()->trans('tags of your issue', [], ZenDesk::DOMAIN_NAME)
                    ]
                ]
            )
            ->add("assignee",
                TextType::class,
                [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('assignee', [], ZenDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => Translator::getInstance()->trans('mail of the assignee', [], ZenDesk::DOMAIN_NAME)
                    ]
                ]
            )
            ->add("organization",
                TextType::class,
                [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('organization', [], ZenDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => Translator::getInstance()->trans('name of your organisation', [], ZenDesk::DOMAIN_NAME)
                    ]
                ]
            )
        ;
    }
}