# Zend Desk

This module allows to retrieve the ticket of a retailer from ZenDesk

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is ZenDesk.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```
composer require thelia/zend-desk-module:~1.0
```

## Usage

First go to https://{sous-domaine}.zendesk.com/admin/apps-integrations/apis/zendesk-api/

![AdminZenDeskAPI](docs/images/admin_zendesk_api.png)

Activate Token Access and add a token API
Copy your token and Save

![AdminZenDeskAPIToken](docs/images/admin_zendesk_api_token.png)

Go to the configuration panel and add your subdomain, api token and Zendesk's username

![ZenDeskConfig](docs/images/zendesk_config.png)

When Connected, your retailers will see their tickets from ZenDesk.

It's using the email of the retailer so be sure to add it in ZenDesk.