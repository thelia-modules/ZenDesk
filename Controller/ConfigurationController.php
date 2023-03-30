<?php

namespace ZenDesk\Controller;

use Thelia\Controller\Admin\AdminController;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Core\Template\ParserContext;
use Thelia\Form\Exception\FormValidationException;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

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
    public function saveConfiguration(ParserContext $parserContext)
    {

        $form = $this->createForm('zen_desk_config_form');
        try {
            $data = $this->validateForm($form)->getData();

            ZenDesk::setConfigValue("zen_desk_api_subdomain", $data["api_subdomain"]);
            ZenDesk::setConfigValue("zen_desk_api_username", $data["api_username"]);
            ZenDesk::setConfigValue("zen_desk_api_token", $data["api_token"]);

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $error_message = $this->createStandardFormValidationErrorMessage($e);
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
        }

        $form->setErrorMessage($error_message);

        $parserContext
            ->addForm($form)
            ->setGeneralError($error_message);

        return $this->generateErrorRedirect($form);
    }
}