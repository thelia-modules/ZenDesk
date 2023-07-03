<?php

namespace ZenDesk\Controller;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse as RedirectResponse;
use Symfony\Component\HttpFoundation\Response as Response;
use Thelia\Controller\Admin\AdminController;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Core\Template\ParserContext;
use Thelia\Form\Exception\FormValidationException;
use ZenDesk\Form\ConfigurationForm;
use ZenDesk\Form\ParametersForm;
use ZenDesk\Utils\ZenDeskManager;
use ZenDesk\ZenDesk;

#[Route('/admin/module/ZenDesk', name: 'zendesk_config')]
class ConfigurationController extends AdminController
{
    public function __construct(protected ZenDeskManager $manager) {
    }

    #[Route('/configuration', name: 'configuration')]
    public function saveConfiguration(ParserContext $parserContext) : RedirectResponse|Response
    {
        $form = $this->createForm(ConfigurationForm::getName());
        try {
            $data = $this->validateForm($form)->getData();

            ZenDesk::setConfigValue("zen_desk_api_subdomain", $data["api_subdomain"]);
            ZenDesk::setConfigValue("zen_desk_api_username", $data["api_username"]);
            ZenDesk::setConfigValue("zen_desk_api_token", $data["api_token"]);

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $error_message = $this->createStandardFormValidationErrorMessage($e);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }

        $form->setErrorMessage($error_message);

        $parserContext
            ->addForm($form)
            ->setGeneralError($error_message);

        return $this->generateErrorRedirect($form);
    }

    #[Route('/parameters', name: 'parameters')]
    public function saveParameters(ParserContext $parserContext) : RedirectResponse|Response
    {
        $form = $this->createForm(ParametersForm::getName());
        try {
            $data = $this->validateForm($form)->getData();

            ZenDesk::setConfigValue("zen_desk_user_rules", $data["user_rules"]);
            ZenDesk::setConfigValue("zen_desk_ticket_type", $data["ticket_type"]);

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $error_message = $this->createStandardFormValidationErrorMessage($e);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }

        $form->setErrorMessage($error_message);

        $parserContext
            ->addForm($form)
            ->setGeneralError($error_message);

        return $this->generateErrorRedirect($form);
    }
}