<?php

namespace AssetsCompressor\Commands;

use AssetsCompressor\AssetsCompressor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AssetsCompressor command line processor.
 */
final class DefaultCommand extends Command
{
    /**
     * Compressor instance.
     * 
     * @var AssetsCompressor
     */
    private $compressor;

    /**
     * Configuration root.
     * 
     * @var string
     */
    private $path_root;

    /**
     * Configure command
     */
    protected function configure()
    {

        // Set application name
        $this->setName('assets-compressor');

        // Add config argument
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED,
            'Configuration yml file path');

        // Add config argument
        $this->addOption('path-root', 'pr', InputOption::VALUE_REQUIRED,
            'Path to the root directory of all the file paths in configuration file');

        // Add config argument
        $this->addOption('path-hash', 'ph', InputOption::VALUE_REQUIRED,
            'Path to the directory where hashes map file will be stored (default: /busters.json)');

        // Add hash argument
        $this->addOption('disable-hashing', 'd', InputOption::VALUE_NONE,
            'Do not add hash suffix to output files');
    }

    /**
     * Execute command.
     * 
     * @param   InputInterface  $input  Command input
     * @param   OutputInterface $output Command output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Create compressor instance
        $this->compressor = new AssetsCompressor();

        // Process configuration option
        $this->processConfig($input, $output);

        // Process path-root option
        $this->processPathRoot($input, $output);

        // Process hashes-root option
        $this->processPathHash($input, $output);

        // Process disable-hashing option
        $this->processDisableHashing($input, $output);
    }

    /**
     * Process command config option.
     * 
     * @param   InputInterface  $input  Command input
     * @param   OutputInterface $output Command output
     */
    private function processConfig(InputInterface $input,
                                   OutputInterface $output)
    {

        // Get configuration file path
        $configuratio_path = $this->getConfigurationFilePath($input);

        // If there is no configuration, throw error
        if (!file_exists($configuratio_path)) {
            $output->writeLn('<error>No configuration file provided. Place assets.yml in your projects root or privite its path using --config=assets.yml option.</error>');
            exit(1);
        }

        // Load configuration to compressor
        $this->compressor->loadConfigurationFile($configuratio_path);
    }

    /**
     * Find configuration file path.
     *
     * @param   InputInterface  $input  Command input
     *
     * @return  string
     */
    private function getConfigurationFilePath(InputInterface $input): string
    {
        if (is_null($this->path_root)) {

            // Possible configuration pathes
            $pathes = [
                $input->getOption('config'),
                getcwd().'/assets.yml',
                getcwd().'/.assets.yml',
                ASSETSCOMPRESSOR_PROJECT_ROOT.'/assets.yml',
                ASSETSCOMPRESSOR_PROJECT_ROOT.'/.assets.yml',
            ];

            // Check each path
            foreach ($pathes AS $file) {
                if (file_exists($file)) {
                    $this->path_root = $file;
                    break;
                }
            }
        }

        return (string) $this->path_root;
    }

    /**
     * Process command path-root option.
     * 
     * @param   InputInterface  $input  Command input
     * @param   OutputInterface $output Command output
     */
    private function processPathRoot(InputInterface $input,
                                     OutputInterface $output)
    {

        // Get configuration root directory
        $root = dirname($this->getConfigurationFilePath($input));

        // Possible root pathes
        $pathes = [
            $input->getOption('path-root'),
            $root,
        ];

        // Check each path
        foreach( $pathes AS $path ) {

            // If path exists, set it as root
            if( is_dir($path) ) {
                $this->compressor->setRootDirectory($path);
                break;
            }
        }
    }

    /**
     * Process command hashes-root option.
     *
     * @param   InputInterface  $input  Command input
     * @param   OutputInterface $output Command output
     */
    private function processPathHash(InputInterface $input,
                                     OutputInterface $output)
    {

        // Get configuration root directory
        $root = dirname($this->getConfigurationFilePath($input));

        // Possible root pathes
        $pathes = [
            $input->getOption('path-hash'),
            $root,
        ];

        // Check each path
        foreach( $pathes AS $path ) {

            // If path exists, set it as root
            if( is_dir($path) ) {
                $this->compressor->setHashesFilePath($path);
                break;
            }
        }
    }

    /**
     * Process command disable-hashing option.
     *
     * @param   InputInterface  $input  Command input
     * @param   OutputInterface $output Command output
     */
    private function processDisableHashing(InputInterface $input,
                                     OutputInterface $output)
    {

        // If option i set, disable files hashing
        if( !is_null($input->getOption('disable-hashing')) ) {
            $this->compressor->setHashing(false);
        }
        
    }
}