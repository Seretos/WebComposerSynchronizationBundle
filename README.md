WebComposerSynchronizationBundle
================================

This bundle create a database schema for project dependency informations and handle the synchronization between the project directory and the database entries.

Installation
------------

Add this repository to your composer.json as below:

```php
{
    ...
    "require": {
        ...
        "web-composer/synchronization-bundle": "0.1.*"
    }
}
```

Add this bundle to your AppKernel.php as below:

```php
...
public function registerBundles()
{
    $bundles = [
        ...
        new WebComposer\SynchronizationBundle\WebComposerSynchronizationBundle(),
    ];

    ...

    return $bundles;
}
...
```

Create the database schema and add the tables for this bundle:

```php
php bin/console doctrine:schema:create
php bin/console doctrine:schema:update --force
```

If you want to synchronize your projects with an http-request you also
require the following lines in your routing.yml

```php
web_composer_synchronization:
    resource: "@WebComposerSynchronizationBundle/Resources/config/routing.yml"
    prefix:   /synchronize/
```

If you want to run this bundle on an specific entity manager, add this lines with
your entity manager to your services.yml:

```php
web_composer.save_service:
    class: WebComposer\SynchronizationBundle\Service\SaveService
    arguments: ["@web_composer.entity_factory","@doctrine.orm.your_entity_manager"]
web_composer.synchronizer:
    class: WebComposer\SynchronizationBundle\Service\SynchronizationService
    arguments: ["@composer_dependency_analyzer","@doctrine.orm.your_entity_manager","@web_composer.save_service","@web_composer.entity_factory"]
```

Usage
-----

In the first step, you must register your project in this application.
with the following command, you can add a project to the system:

```php
php bin/console web-composer:create-project projectName "/path/to/project"
```

alternative you can use the fixture command to install the current project
(this command requires the doctrine/doctrine-fixtures-bundle package)

```php
php bin/console doctrine:fixture:load --append
```

Now you can synchronize your project. you can use the console command, or create an http-request (see routing.yml)

```php
php bin/console web-composer:synchronize-project projectName
```

```php
http://your.url.de:port/synchronize/yourProjectName
```

Tests
-----

to run the unit tests execute the following command:
(to use vendor\bin\phpunit you require the phpunit/phpunit package)

```php
//phpunitcommand -c /path/to/package/phpunit.xml.dist
vendor\bin\phpunit -c vendor\web-composer\synchronization-bundle\phpunit.xml.dist
```

to run the integration test you need the routing.yml entry!

```php
vendor\bin\phpunit -c vendor\web-composer\synchronization-bundle\integration.xml.dist
```