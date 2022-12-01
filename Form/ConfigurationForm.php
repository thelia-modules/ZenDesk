<?php

namespace ZendDesk\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use ZendDesk\ZendDesk;

class ConfigurationForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'api_subdomain',
                TextType::class, [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('subdomain', [], ZendDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => 'api_subdomain',
                        'help' => Translator::getInstance()->trans("https://{subdomain}.zendesk.com", [], ZendDesk::DOMAIN_NAME),
                    ],
                    'data' => ZendDesk::getConfigValue('zen_desk_api_subdomain')
                ]
            )
            ->add(
                'api_token',
                TextType::class, [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('api_token', [], ZendDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => 'api_token',
                        'help' => Translator::getInstance()->trans("Go to zendesk admin panel, Search API Zendesk -> Add a token API", [], ZendDesk::DOMAIN_NAME),
                    ],
                    'data' => ZendDesk::getConfigValue('zen_desk_api_token')
                ]
            )
            ->add(
                'api_username',
                TextType::class, [
                    'required' => true,
                    'label' => Translator::getInstance()->trans('api_username', [], ZendDesk::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => 'api_username',
                        'help' => Translator::getInstance()->trans("Admin Zendesk username", [], ZendDesk::DOMAIN_NAME),
                    ],
                    'data' => ZendDesk::getConfigValue('zen_desk_api_username')
                ]
            );
    }

    public static function getName()
    {
        return 'zen_desk_config_form';
    }
}