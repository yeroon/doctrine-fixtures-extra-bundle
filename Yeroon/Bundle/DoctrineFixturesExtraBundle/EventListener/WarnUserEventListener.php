<?php

namespace Yeroon\Bundle\DoctrineFixturesExtraBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleCommandEvent;

class WarnUserEventListener {

    public function onExecute(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        $output = $event->getOutput();

        // warn user, ask to continue

        $confirmation = $command->getHelper('dialog')->askConfirmation($output, '<question>WARNING! You are about to drop the database(s) and rebuild the schema(s). ALL data will be lost. Are you sure you wish to continue? (y/n)</question>', false);
        if ($confirmation === false) {
            $command->writeError($output, 'Rebuild schema and load fixtures cancelled!');
            exit(1);
        }
    }
}
