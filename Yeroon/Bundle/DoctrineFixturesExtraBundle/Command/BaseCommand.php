<?php

namespace Yeroon\Bundle\DoctrineFixturesExtraBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends ContainerAwareCommand
{

    /**
     * Checks if a Bundle is loaded
     *
     * @param string $bundleName e.g. 'DoctrineMongoDBBundle'
     *
     * @return bool
     */
    protected function isBundleLoaded($bundleName)
    {
        try {
            $this->getApplication()->getKernel()->getBundle($bundleName);
            $loaded = true;
        } catch (\InvalidArgumentException $e) {
            $loaded = false;
        }

        return $loaded;
    }

    public function writeError(OutputInterface $output, $message)
    {
        $this->write($output, $message, 'error');
    }

    public function writeQuestion(OutputInterface $output, $message)
    {
        $this->write($output, $message, 'question');
    }

    public function write(OutputInterface $output, $message, $type)
    {
        $output->writeln(sprintf('<%1$s>[FixtureExtra] %2$s</%1$s>', $type, $message));
    }
}
