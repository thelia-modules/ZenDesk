<?php

namespace ZendDesk\Controller;

use Thelia\Controller\Admin\AdminController;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Form\Exception\FormValidationException;
use ZendDesk\Utils\ZenDeskManager;
use ZendDesk\ZendDesk;

/**
 * @Route("/admin/module/ZenDesk", name="zendeskdelivery_config")
 */
class ConfigurationController extends AdminController
{
    /**
     * @var ZenDeskManager
     */
    protected ZenDeskManager $manager;

    public function __construct(ZenDeskManager $Manager)
    {
        $this->manager = $Manager;
    }

    /**
     * @Route("/configuration", name="configuration")
     */
    public function saveConfiguration()
    {

        $form = $this->createForm('zen_desk_config_form');
        try {
            $data = $this->validateForm($form)->getData();

            ZendDesk::setConfigValue("zen_desk_api_subdomain", $data["api_subdomain"]);
            ZendDesk::setConfigValue("zen_desk_api_username", $data["api_username"]);
            ZendDesk::setConfigValue("zen_desk_api_token", $data["api_token"]);

            $subdomain = $data["api_subdomain"];
            $username = $data["api_username"];
            $token = $data["api_token"];

            $this->manager->getTicketsUser($subdomain, $username, $token, $username);

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $error_message = $this->createStandardFormValidationErrorMessage($e);
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
        }

        $form->setErrorMessage($error_message);

        $this->getParserContext()
            ->addForm($form)
            ->setGeneralError($error_message);

        return $this->generateErrorRedirect($form);
    }
}