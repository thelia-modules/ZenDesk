<?php

namespace ZenDesk\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use ZenDesk\ZenDesk;

class ConfigurationForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'api_subdomain',
                TextType::class, [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('subdomain', [], ZenDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => 'api_subdomain',
                        'help' => Translator::getInstance()->trans("https://{subdomain}.zendesk.com", [], ZenDesk::DOMAIN_NAME),
                    ],
                    'data' => ZenDesk::getConfigValue('zen_desk_api_subdomain')
                ]
            )
            ->add(
                'api_token',
                TextType::class, [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('api_token', [], ZenDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => 'api_token',
                        'help' => Translator::getInstance()->trans("Go to zendesk admin panel, Search API Zendesk -> Add a token API", [], ZenDesk::DOMAIN_NAME),
                    ],
                    'data' => ZenDesk::getConfigValue('zen_desk_api_token')
                ]
            )
            ->add(
                'api_username',
                TextType::class, [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('api_username', [], ZenDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => 'api_username',
                        'help' => Translator::getInstance()->trans("Admin Zendesk username", [], ZenDesk::DOMAIN_NAME),
                    ],
                    'data' => ZenDesk::getConfigValue('zen_desk_api_username')
                ]
            );
    }
}