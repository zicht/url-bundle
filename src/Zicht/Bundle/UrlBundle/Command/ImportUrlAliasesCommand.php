<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

class ImportUrlAliasesCommand extends ContainerAwareCommand
{
    /**
     * Defines the possible input values for the command and their associated (i.e. mapped) behavior
     */
    const REDIRECT_TYPE_MAPPING = [
        '0' => UrlAlias::REWRITE,
        '301' => UrlAlias::MOVE,
        '302' => UrlAlias::ALIAS,
        'rewrite' => UrlAlias::REWRITE,
        'move' => UrlAlias::MOVE,
        'alias' => UrlAlias::ALIAS,
    ];

    /**
     * Defines the possible input values for the command and their associated (i.e. mapped) behavior
     */
    const DEFAULT_CONFLICTING_PUBLIC_URL_STRATEGY_MAPPING = [
        'keep' => Aliasing::STRATEGY_KEEP,
        'overwrite' => Aliasing::STRATEGY_OVERWRITE,
        'suffix' => Aliasing::STRATEGY_SUFFIX,
    ];

    /**
     * Defines the possible input values for the command and their associated (i.e. mapped) behavior
     */
    const DEFAULT_CONFLICTING_INTERNAL_URL_STRATEGY_MAPPING = [
        'ignore' => Aliasing::STRATEGY_IGNORE,
        'move-new-to-previous' => Aliasing::STRATEGY_MOVE_NEW_TO_PREVIOUS,
        'move-previous-to-new' => Aliasing::STRATEGY_MOVE_PREVIOUS_TO_NEW,
    ];

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('zicht:url:import-aliases')
            ->setDescription('Import multiple url aliases from a source file')
            ->setHelp(
                'This command can parse csv files that follow the following syntax:

    PUBLICURL, INTERNALURL, TYPE, CONFLICTINGPUBLICURLSTRATEGY, CONFLICTINGINTERNALURLSTRATEGY
    /home, /nl/page/1
    /also-home, /nl/page/1

Note that the first line can be ignored using "--skip-header"
TYPE, CONFLICTINGPUBLICURLSTRATEGY, and CONFLICTINGINTERNALURLSTRATEGY are optional.'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Filepath of import CSV file'
            )
            ->addOption(
                'skip-header',
                null,
                InputOption::VALUE_NONE,
                'Skip the first line of the input file'
            )
            ->addOption(
                'default-redirect-type',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('Type of redirect, one of: %s', join(', ', array_keys(self::REDIRECT_TYPE_MAPPING))),
                'alias'
            )
            ->addOption(
                'default-conflicting-public-url-strategy',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('How to handle conflicting public url, one of: %s', join(', ', array_keys(self::DEFAULT_CONFLICTING_PUBLIC_URL_STRATEGY_MAPPING))),
                'keep'
            )
            ->addOption(
                'default-conflicting-internal-url-strategy',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('How to handle conflicting internal url, one of: %s', join(', ', array_keys(self::DEFAULT_CONFLICTING_INTERNAL_URL_STRATEGY_MAPPING))),
                'move-new-to-previous'
            )
            ->addOption(
                'csv-delimiter',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('Delimiter used when parsing a line of csv'),
                ','
            )
            ->addOption(
                'csv-enclosure',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('Enclosure used when parsing a line of csv'),
                '"'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $defaultRedirectType = self::REDIRECT_TYPE_MAPPING[$input->getOption('default-redirect-type')];
        $defaultConflictingPublicUrlStrategy = self::DEFAULT_CONFLICTING_PUBLIC_URL_STRATEGY_MAPPING[$input->getOption('default-conflicting-public-url-strategy')];
        $defaultConflictingInternalUrlStrategy = self::DEFAULT_CONFLICTING_INTERNAL_URL_STRATEGY_MAPPING[$input->getOption('default-conflicting-internal-url-strategy')];
        $csvDelimiter = $input->getOption('csv-delimiter');
        $csvEnclosure = $input->getOption('csv-enclosure');

        $aliasingService = $this->getContainer()->get('zicht_url.aliasing');
        $flush = $aliasingService->setIsBatch(true);

        $handle = fopen($input->getArgument('file'), 'r');
        if (false === $handle) {
            throw new \Exception('Can not open input file');
        }

        $lineNumber = 0;

        if ($input->getOption('skip-header')) {
            $lineNumber++;
            $data = fgetcsv($handle, null, $csvDelimiter, $csvEnclosure);
            if (false === $data) {
                throw new \Exception(sprintf('Can not read line %s in input file', $lineNumber));
            }
        }

        while ($data = fgetcsv($handle, null, $csvDelimiter, $csvEnclosure)) {
            $lineNumber++;

            if (false === $data) {
                throw new \Exception(sprintf('Can not read line %s in input file', $lineNumber));
            }

            if (null === $data || [null] === $data) {
                // skip empty line
                continue;
            }

            if (sizeof($data) < 2) {
                throw new \Exception('Every line in the file must have at least the publicUrl and internalUrl');
            }

            $publicUrl = trim($data[0]);
            $internalUrl = trim($data[1]);
            $type = $this->parseInputToMapping(self::REDIRECT_TYPE_MAPPING, $data, 2, $defaultRedirectType);
            $conflictingPublicUrlStrategy = $this->parseInputToMapping(self::DEFAULT_CONFLICTING_PUBLIC_URL_STRATEGY_MAPPING, $data, 3, $defaultConflictingPublicUrlStrategy);
            $conflictingInternalUrlStrategy = $this->parseInputToMapping(self::DEFAULT_CONFLICTING_INTERNAL_URL_STRATEGY_MAPPING, $data, 3, $defaultConflictingInternalUrlStrategy);

            $realInternalUrl = $aliasingService->hasInternalAlias($internalUrl);
            if (null !== $realInternalUrl && $internalUrl !== $realInternalUrl) {
                // the $internalUrl given in the csv is actually known as a public url in our system.
                // the $publicUrl given in the csv should redirect directly to this known public url.
                $redirectDescription = sprintf('%s -> %s -> %s', $publicUrl, $internalUrl, $realInternalUrl);
                $internalUrl = $realInternalUrl;
            } else {
                $redirectDescription = sprintf('%s -> %s', $publicUrl, $internalUrl);
            }

            // perform aliasing
            $aliasingService->addAlias($publicUrl, $internalUrl, $type, $conflictingPublicUrlStrategy, $conflictingInternalUrlStrategy);
            $output->writeln(sprintf('%s  %s  %s', $lineNumber, $type, $redirectDescription));
        }

        $flush();
    }

    /**
     * Return the parsed value from $data[$index]
     *
     * @param array $mapping
     * @param array $data
     * @param integer $index
     * @param integer $default
     * @return integer
     * @throws \Exception
     */
    private function parseInputToMapping($mapping, $data, $index, $default)
    {
        if (array_key_exists($index, $data)) {
            $value = strtolower(trim($data[$index]));

            if ('' === $value) {
                return $default;
            }

            if (array_key_exists($value, $mapping)) {
                return $mapping[$value];
            }

            throw new \Exception('Could not parse');
        }

        return $default;
    }
}
