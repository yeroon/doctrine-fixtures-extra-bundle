<?php


namespace Yeroon\Bundle\DoctrineFixturesExtraBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class CloseConnectionEventListener
{
    /**
     * Closes the database connection (Has to be done for MySQL 5.5+ after database has been dropped)
     *
     * @param ConsoleCommandEvent $event
     */
    public function onExecute(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        $input   = $event->getInput();
        /** @var $doctrine \Doctrine\Bundle\DoctrineBundle\Registry */
        $doctrine = $command->getApplication()->getKernel()->getContainer()->get('doctrine');
        /** @var $em EntityManager */
        $em = $doctrine->getManager($input->getOption('em'));
        $conn = $em->getConnection();

        $conn->close();
    }
}
