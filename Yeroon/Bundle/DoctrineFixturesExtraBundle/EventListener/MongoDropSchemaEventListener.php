<?php

namespace Yeroon\Bundle\DoctrineFixturesExtraBundle\EventListener;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

class MongoDropSchemaEventListener {

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onExecute(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        $input   = $event->getInput();
        $output  = $event->getOutput();

        $mongoBundleLoaded = $this->isDoctrineMongoDbBundleLoaded($command->getApplication()->getKernel());

        // drop mongo collection
        if($mongoBundleLoaded){
            $exitCode = $this->executeDoctrineMongoDbSchemaDrop($command->getApplication(), $input, $output);

            if($exitCode > 0){
                $command->writeError($output, 'FAIL - Aborted because mongodb database could not be dropped.');
                exit($exitCode);
            }
        }
    }

    protected function executeDoctrineMongoDbSchemaDrop(Application $application, OutputInterface $output)
    {
        $command = $application->find('doctrine:mongodb:schema:drop');

        $arguments = array(
            'command' => 'doctrine:mongodb:schema:drop',
        );

        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }

    /**
     * @return bool
     */
    protected function isDoctrineMongoDbBundleLoaded(Kernel $kernel)
    {
        try {
            $kernel->getBundle('DoctrineMongoDBBundle');
            $mongoBundleLoaded = true;
        } catch (\InvalidArgumentException $e) {
            $mongoBundleLoaded = false;
        }

        return $mongoBundleLoaded;
    }
}

