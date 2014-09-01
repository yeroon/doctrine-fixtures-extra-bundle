# Yeroon Doctrine Fixtures Extra Bundle

The Yeroon Doctrine Fixtures Extra Bundle adds a command to your Symfony application to
recreate your database schema and to load fixtures in one go. When run, it:

 - Validates your schema (`doctrine:schema:validate`)
 - When valid, drops the database (`doctrine:database:drop`)
 - Recreates the database (`doctrine:database:create`)
 - Rebuilds the schema (`doctrine:schema:update`)
 - And finally loads your fixtures (`doctrine:fixtures:load`)

This way, only _one_ command is needed to re-create an up-to-date database with data for your `dev` environment. Other
environments are supported, but not recommended (Data *will* be lost, ofcourse).

## Features

 - Supports multiple Entity Managers.
 - Event-driven. Event Listeners can be attached to extend functionality (see 'Events').
 - Aborts when your schema is not valid.
 - Built on top of existing Doctrine commands.
 - Doctrine MongoDB ODM support thorugh an additional command.
 - Supports multiple Document Managers for Doctrine MongoDB ODM.

## Dependencies

 - DoctrineBundle
 - DoctrineFixturesBundle

## Installation

### 1. Add yeroon/doctrine-fixtures-extra-bundle to your composer.json:

    "require": {
        ...
        "yeroon/doctrine-fixtures-extra-bundle": "~0.1.*",
    }

### 2. Add DoctrineFixturesExtraBundle to your AppKernel:

    // app/AppKernel.php
    public function registerBundles()
    {
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            // ..
            $bundles[] = new Yeroon\Bundle\DoctrineFixturesExtraBundle\YeroonDoctrineFixturesExtraBundle();
        }
    }

  The example above adds the Bundle in the `dev` and `test` environments only (recommended).

# Events

The command triggers six events. The events, in order, are:

|event name          | description |
|--------------------|-------------|
| init               | Triggered at the start of the command. Useful to add checks or validation. For example, the"`WarnUserEventListener` is configured to listen to this event. |
| on_validate_schema | Happens when the schema gets validated (Immediately after `doctrine:schema:validate`) |
| on_drop_database   | Happens when the database will be dropped (Immediately after `doctrine:database:drop`) |
| on_create_database | Happens when the database is (re)created (Immediately after `doctrine:database:create`) |
| on_schema_update   | Happens when the schema gets updated (Immediately after `doctrine:schema:update`) |
| on_load_fixture    | Happens when the fixtures are loaded (Immediately after `doctrine:fixtures:load`) |

# Examples

## Example 1: Standard behaviour

Below is a sample output of the command:

    $ php app/console yeroon:doctrine:fixtures:rebuildandload
    WARNING! You are about to drop the database(s) and rebuild the schema(s). ALL data will be lost. Are you sure you wish to continue? (y/n)y
    [Mapping]  OK - The mapping files are correct.
    [Database] OK - The database schema is in sync with the mapping files.
    Dropped database for connection named `my_database`
    Created database for connection named `my_database`
    Updating database schema...
    Database schema updated successfully! "5" queries were executed
      > purging database
      > loading Productr\Bundle\CatalogBundle\DataFixtures\ORM\ExampleFixtureLoader

It first asks if you want to continue. If yes, it will check the current mapping against your database with the
`doctrine:schema:validate` command. If it is not valid the Command will abort. If it is valid, the Command will drop the
entire database and recreate it using the `doctrine:database:drop` and `doctrine:database:create` commands. When that
is done, the command `doctrine:schema:update` will be executed to create the schema. Finally, your fixtures will be loaded
through the `doctrine:fixtures:load`. Now you have a database in sync with your schema and filled with fixture data!

## Example 2: Attach EventListeners to purge a Solr index and insert new data

This example shows you how you can can attach event listeners that deletes data from your Solr core, and inserts new data
when fixtures are loaded.

Write custom Event Listener to delete Solr documents:

    // file: src/Acme/YourBundle/EventListener/SolrPurgeEventListener.php
    <?php

    namespace Acme/YourBundle/EventListener;

    use Symfony\Component\Console\Event\ConsoleCommandEvent;
    use Solarium\Client;

    class SolrPurgeEventListener {

        protected $client;

        public function __construct(Client $client){
            $this->client = $client;
        }

        public function onExecute(ConsoleCommandEvent $event){
            $update = $this->client->createUpdate();
            $update->addDeleteQuery('*:*');
            $update->addCommit();
            $client->update($update);
        }
    }

Register EventListener:

    // file: src/Acme/YourBundle/Resources/config/services.xml
    <service id="acme_your.event_listener.solr_purge" class="Acme/YourBundle/EventListener/SolrPurgeEventListener">
        <tag name="kernel.event_listener" event="yeroon.doctrine_fixtures_extra.on_drop_database" method="onExecute" />
    </service>

This will delete all your documents from your Solr core when the database is dropped.

Write custom Event Listener to load fixtures data from your Entities into your Solr core:

    // file: src/Acme/YourBundle/EventListener/SolrLoadDocumentsEventListener.php
    <?php

    namespace Acme/YourBundle/EventListener;

    use Symfony\Component\Console\Event\ConsoleCommandEvent;
    use Solarium\Client;

    class SolrLoadDocumentsEventListener {

        protected $manager;

        public function __construct(SomeManager $manager){
            $this->manager = $manager;
        }

        public function onExecute(ConsoleCommandEvent $event){

            //execute some manager
            $manager->loadDocumentsIntoSolr();
        }
    }

Register another EventListener:

    // file: src/Acme/YourBundle/Resources/config/services.xml
    <service id="acme_your.event_listener.solr_load_documents" class="Acme/YourBundle/EventListener/SolrLoadDocumentsEventListener">
        <tag name="kernel.event_listener" event="yeroon.doctrine_fixtures_extra.on_load_fixtures" method="onExecute" />
    </service>

## Doctrine MongoDB ODM support

The Bundle adds an additional command (`yeroon:doctrine:mongodb:fixtures:rebuildandload`) that rebuilds your MongoDb
database and collections. Also loads fixtures.
