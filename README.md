WebComposerSynchronizationBundle
================================

Installation
------------

Add this repository to your composer.json as below:

```php
{
    ...
    "repositories":[
        ...
        ,
        {
            "type": "git",
            "url": "https://github.com/Seretos/WebComposerSynchronizationBundle"
        }
    ],
    ...
    "require-dev": {
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

Usage
-----

In the first step, you must register your project in this application.
with the following command, you can add a project to the system:

```php
php bin/console web-composer:create-project projectName "/path/to/project"
```

alternative you can use the fixture command to install the current project

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
