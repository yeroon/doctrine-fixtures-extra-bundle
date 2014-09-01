<?php

namespace Yeroon\Bundle\DoctrineFixturesExtraBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class MongoDbRebuildAndLoadCommand extends BaseCommand
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
            ->setName('yeroon:doctrine:mongodb:fixtures:rebuildandload')
            ->setDescription('Rebuild schema and load fixtures in one go.')
            ->addOption(
                'dm',
                'default',
                InputOption::VALUE_REQUIRED,
                'The document manager to use for this command.'
            )
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');

        $eventDispatcher->dispatch(
            'yeroon.doctrine_mongodb_fixtures_extra.on_init',
            new ConsoleCommandEvent($this, $input, $output)
        );

        $eventDispatcher->dispatch(
            'yeroon.doctrine_mongodb_fixtures_extra.on_validate_schema',
            new ConsoleCommandEvent($this, $input, $output)
        );

        $this->executeDoctrineMongoDbSchemaDrop($input, $output);

        $eventDispatcher->dispatch(
            'yeroon.doctrine_mongodb_fixtures_extra.on_drop_schema',
            new ConsoleCommandEvent($this, $input, $output)
        );

        $this->executeDoctrineMongoDbSchemaCreate($input, $output);

        $eventDispatcher->dispatch(
            'yeroon.doctrine_mongodb_fixtures_extra.on_create_schema',
            new ConsoleCommandEvent($this, $input, $output)
        );

        $this->executeDoctrineMongoDbFixturesLoad($input, $output);

        $eventDispatcher->dispatch(
            'yeroon.doctrine_mongodb_fixtures_extra.on_load_fixtures',
            new ConsoleCommandEvent($this, $input, $output)
        );
    }

    /**
     * @param OutputInterface $output
     */
    protected function executeDoctrineMongoDbSchemaDrop(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:mongodb:schema:drop');

        $arguments = [
            'command' => 'doctrine:mongodb:schema:drop',
            '--dm' => $input->getOption('dm')
        ];

        $input = new ArrayInput($arguments);

        $exitCode = $command->run($input, $output);

        if($exitCode > 0){
            $output->writeln('<error>[FixtureExtra] FAIL - Aborted because mongodb database could not be dropped.</error>');
            exit($exitCode);
        }
    }

    /**
     * @param OutputInterface $output
     *
     * @return int|mixed
     */
    protected function executeDoctrineMongoDbSchemaCreate(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:mongodb:schema:create');

        $arguments = [
            'command' => 'doctrine:mongodb:schema:create',
            '--dm' => $input->getOption('dm'),
        ];

        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }

    /**
     * @param OutputInterface $output
     *
     * @return int|mixed
     */
    protected function executeDoctrineMongoDbFixturesLoad(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:mongodb:fixtures:load');

        $arguments = [
            'command' => 'doctrine:mongodb:fixtures:load',
            '--dm' => $input->getOption('dm')
        ];

        $input = new ArrayInput($arguments);
        // don't prompt the user here
        $input->setInteractive(false);

        return $command->run($input, $output);
    }
}
