<?php

namespace Yeroon\Bundle\DoctrineFixturesExtraBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildAndLoadCommand extends BaseCommand
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
            ->setName('yeroon:doctrine:fixtures:rebuildandload')
            ->setDescription('Rebuild schema and load fixtures in one go.')
            ->addOption(
                'em',
                null,
                InputOption::VALUE_REQUIRED,
                'The entity manager to use for this command.'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $em EntityManager */
        $doctrine        = $this->getContainer()->get('doctrine');
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $em              = $doctrine->getManager($input->getOption('em'));
        $conn            = $em->getConnection();

        $eventDispatcher->dispatch(
            'yeroon.doctrine_fixtures_extra.init',
            new ConsoleCommandEvent($this, $input, $output)
        );

        $this->executeDoctrineSchemaValidate($input, $output);

        $eventDispatcher->dispatch(
            'yeroon.doctrine_fixtures_extra.on_schema_validate',
            new ConsoleCommandEvent($this, $input, $output)
        );

        $this->executeDoctrineDatabaseDrop($input, $output, $conn);

        $eventDispatcher->dispatch(
            'yeroon.doctrine_fixtures_extra.on_drop_database',
            new ConsoleCommandEvent($this, $input, $output)
        );

        $this->executeDoctrineDatabaseCreate($input, $output, $conn);

        $eventDispatcher->dispatch(
            'yeroon.doctrine_fixtures_extra.on_create_database',
            new ConsoleCommandEvent($this, $input, $output)
        );

        $this->executeDoctrineSchemaUpdate($input, $output);

        $eventDispatcher->dispatch(
            'yeroon.doctrine_fixtures_extra.on_update_schema',
            new ConsoleCommandEvent($this, $input, $output)
        );

        $this->executeDoctrineFixturesLoad($input, $output);

        $eventDispatcher->dispatch(
            'yeroon.doctrine_fixtures_extra.on_load_fixtures',
            new ConsoleCommandEvent($this, $input, $output)
        );

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function executeDoctrineSchemaValidate(InputInterface $input, OutputInterface $output)
    {
        $command   = $this->getApplication()->find('doctrine:schema:validate');
        $arguments = array(
            'command' => 'doctrine:schema:validate',
            '--em'    => $input->getOption('em')
        );
        $input     = new ArrayInput($arguments);
        $exitCode  = $command->run($input, $output);
        if ($exitCode > 0) {
            $this->writeError($output, 'FAIL - Aborted because the schema is invalid.');
            exit($exitCode);
        }
    }

    /**
     * @param InputInterface $input
     * @param                $output
     * @param Connection     $conn
     */
    protected function executeDoctrineDatabaseDrop(InputInterface $input, $output, Connection $conn)
    {
        $command   = $this->getApplication()->find('doctrine:database:drop');
        $arguments = array(
            'command'      => 'doctrine:database:drop',
            '--connection' => $this->findNameForConnection($conn),
            '--force'      => true,
        );
        $input     = new ArrayInput($arguments);
        $exitCode  = $command->run($input, $output);
        if ($exitCode > 0) {
            $this->writeError($output, 'FAIL - Aborted because database could not be dropped.');
            exit($exitCode);
        }
    }

    /**
     * @param InputInterface $input
     * @param                $output
     * @param Connection     $conn
     */
    protected function executeDoctrineDatabaseCreate(InputInterface $input, $output, Connection $conn)
    {
        $command   = $this->getApplication()->find('doctrine:database:create');
        $arguments = array(
            'command'      => 'doctrine:database:create',
            '--connection' => $this->findNameForConnection($conn),
        );
        $input     = new ArrayInput($arguments);
        $exitCode  = $command->run($input, $output);
        if ($exitCode > 0) {
            $this->writeError($output, 'FAIL - Aborted because database could not be created.');
            exit($exitCode);
        }
    }

    /**
     * @param InputInterface $input
     * @param                $output
     */
    protected function executeDoctrineSchemaUpdate(InputInterface $input, $output)
    {
        $command   = $this->getApplication()->find('doctrine:schema:update');
        $arguments = array(
            'command' => 'doctrine:schema:update',
            '--em'    => $input->getOption('em'),
            '--force' => true,
        );
        $input     = new ArrayInput($arguments);
        $exitCode  = $command->run($input, $output);
        if ($exitCode > 0) {
            $this->writeError($output, 'FAIL - Aborted because database schema could not be updated.');
            exit($exitCode);
        }
    }


    /**
     * @param InputInterface $input
     * @param                $output
     */
    protected function executeDoctrineFixturesLoad(InputInterface $input, $output)
    {
        $command   = $this->getApplication()->find('doctrine:fixtures:load');
        $em        = $input->getOption('em');
        $arguments = array(
            'command' => 'doctrine:fixtures:load',
        );
        if (null !== $em) {
            $arguments['--em'] = $em;
        }
        $input = new ArrayInput($arguments);
        // don't prompt the user here
        $input->setInteractive(false);
        $exitCode = $command->run($input, $output);
        if ($exitCode > 0) {
            $this->writeError($output, 'FAIL - Aborted because database fixtures could not be loaded.');
            exit($exitCode);
        }
    }

    /**
     * @param Connection $conn
     *
     * @return int|string The Connection name
     * @throws \InvalidArgumentException
     */
    protected function findNameForConnection(Connection $conn)
    {
        $doctrine    = $this->getContainer()->get('doctrine');
        $connections = $doctrine->getConnections();
        foreach ($connections as $name => $connection) {
            if ($conn === $connection) {
                return $name;
            }
        }
        throw new \InvalidArgumentException("Connection not found in registered connections.");
    }
}


