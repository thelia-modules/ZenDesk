# Zend Desk

This module allows to retrieve the ticket of a retailer from ZenDesk

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is ZendDesk.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```
composer require thelia/zend-desk-module:~1.0
```

## Usage

Go to the configuration panel and add your subdomain, api token and Zendesk's username

When Connected your retailers will see their tickets from ZendDesk.
It's using the email of the retailer so be sure to add it in ZenDesk.