<?php

namespace Yeroon\Bundle\DoctrineFixturesExtraBundle\Event;

final class DoctrineFixturesExtraEvents
{
    /**
     * Happens at the start of the command
     */
    const ON_INIT = 'yeroon.doctrine_fixtures_extra.on_init';

    /**
     * Happens when the schema gets validated
     */
    const ON_SCHEMA_VALIDATE = 'yeroon.doctrine_fixtures_extra.on_schema_validate';

    /**
     * Happens when the database gets dropped
     */
    const ON_SCHEMA_DROP = 'yeroon.doctrine_fixtures_extra.on_schema_drop';

    /**
     * Happens when the database is created
     */
    const ON_SCHEMA_CREATE = 'yeroon.doctrine_fixtures_extra.on_schema_create';

    /**
     * Happens when the schema is updated
     */
    const ON_SCHEMA_UPDATE = 'yeroon.doctrine_fixtures_extra.on_schema_update';

    /**
     * Happens when the fixtures are loaded
     */
    const ON_FIXTURE_LOAD = 'yeroon.doctrine_fixtures_extra.on_fixture_load';
}
