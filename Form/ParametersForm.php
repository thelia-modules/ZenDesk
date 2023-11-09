<?php

namespace ZenDesk\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use ZenDesk\ZenDesk;

class ParametersForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'user_rules',
                ChoiceType::class,
                [
                    'choices'  => [
                        Translator::getInstance()->trans("Read-Only", [], ZenDesk::DOMAIN_NAME) => false,
                        Translator::getInstance()->trans("Read-Write-Update", [], ZenDesk::DOMAIN_NAME) => true,
                    ],
                    'required'   => true,
                    'label' =>Translator::getInstance()->trans("User Rules", [], ZenDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => 'user_rules',
                        'help' => Translator::getInstance()->trans(
                            "the user can only read his tickets/comments or can create is own tickets and edit comment",
                            [],
                            ZenDesk::DOMAIN_NAME),
                        ],
                    'data' => ZenDesk::getConfigValue('zen_desk_user_rules')
                ]
            )
            ->add(
                'ticket_type',
                ChoiceType::class,
                [
                    'choices'  => [
                        Translator::getInstance()->trans("Assigned-Only", [], ZenDesk::DOMAIN_NAME) => "assigned",
                        Translator::getInstance()->trans("Requested-Only", [], ZenDesk::DOMAIN_NAME) => "requested",
                        Translator::getInstance()->trans("All-Tickets", [], ZenDesk::DOMAIN_NAME) => "all"
                    ],
                    'label' => Translator::getInstance()->trans("Ticket Type", [], ZenDesk::DOMAIN_NAME),
                    'required'   => true,
                    'label_attr' => [
                        'for' => 'ticket_type',
                        'help' => Translator::getInstance()->trans(
                            "type of ticket the user should see",
                            [],
                            ZenDesk::DOMAIN_NAME
                        )
                    ],
                    'data' => ZenDesk::getConfigValue('zen_desk_ticket_type')
                ]
            )
            ->add(
                'column_hide',
                ChoiceType::class,
                [
                    'choices'  => [
                        Translator::getInstance()->trans("Hide assigned column", [], ZenDesk::DOMAIN_NAME) => "assigned hide",
                        Translator::getInstance()->trans("Hide requested column", [], ZenDesk::DOMAIN_NAME) => "requested hide",
                        Translator::getInstance()->trans("Hide nothing", [], ZenDesk::DOMAIN_NAME) => "nothing hide"
                    ],
                    'label' => Translator::getInstance()->trans("Hidden column", [], ZenDesk::DOMAIN_NAME),
                    'required'   => true,
                    'label_attr' => [
                        'for' => 'ticket_type',
                        'help' => Translator::getInstance()->trans(
                            "hide a column (only work with ticket type : all)",
                            [],
                            ZenDesk::DOMAIN_NAME
                        )
                    ],
                    'data' => ZenDesk::getConfigValue('zen_desk_hide_column')
                ]
            )
            ->add(
                'private_comment',
                CheckboxType::class,
                [
                    'label'    => Translator::getInstance()->trans("Show private comments ?", [], ZenDesk::DOMAIN_NAME),
                    'required' => false,
                    'data' => (bool)ZenDesk::getConfigValue('zen_desk_show_private_comment')
                ]
            )
        ;
    }
}