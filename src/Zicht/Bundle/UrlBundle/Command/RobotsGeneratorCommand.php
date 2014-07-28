<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zicht\Bundle\UrlBundle\Robots\ListBuilder;

class RobotsGeneratorCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('zicht:generate:robots')
            ->setDescription('Will generate a robots.txt')
            ->addOption('dry-run','d', InputOption::VALUE_NONE, 'Do a dry run')
            ->addOption('exclude','x', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'exclude routes', array())
            ->setHelp('
    This command will generate a robots.txt file for the root
    of the web folder.

    This will check the routing and UrlAlias entities for routes. Routes can be
    excluded with the --exclude option (x) or by configuring the config. Example:

    .........

    zicht_url:
        robots:
            exclude_patterns:
                - /
                - /(media|page|deree|page|story|goto|memorix|thread|maps|location|json|rss|chat|register)*
                - /log(in|out)*
                - /admin*
                - /_*

    ............

            ');

    }


    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ListBuilder $listBuilder */
        $listBuilder = $this->getContainer()->get('zicht_url.robots.list_builder');
        $dryRun      = $input->getOption('dry-run');

        if ($dryRun) {
            $output->write($listBuilder->getList(true, true));
        } else {
            $output->writeln(sprintf('Updated robots.txt (%s bytes)', $listBuilder->writeList()));
        }

    }
}