<?php

/**
 * @name    AssetsCompressor/AssetsCompressor
 * @link    https://github.com/AssetsCompressor/AssetsCompressor
 * @author  Artur Stępień
 */

namespace AssetsCompressor;

use Exception;
use MatthiasMullie\Minify\JS AS JSProcessor;
use Symfony\Component\Yaml\Yaml;
use tubalmartin\CssMin\Minifier as CSSProcessor;

/**
 * Combine and compress static files (CSS/JS) into packages and compress them.
 */
class AssetsCompressor
{
    /**
     * Holds library configuration loaded from YAML file
     * 
     * @var array
     */
    protected $config = [];

    /**
     * Configuration file root directory. It will be treated as a root directory
     * for all the paths in configuration file.
     *
     * @var string
     */
    protected $path_root = '';

    /**
     * Where to store hashes json file if versioning is enabled.
     * 
     * @var string
     */
    protected $path_hashes = '';

    /**
     * Entry point hashes.
     *
     * @var array[string]
     */
    protected $hashes = [];

    /**
     * Should minified files be named using version hash?
     * 
     * @var bool
     */
    protected $versioning = true;

    /**
     * Create compressor instance.
     *
     * @param   string  $config_path    Absolute configuration file path.
     * @param   bool    $versioning     Save minified output files with version tag?
     */
    public function __construct(string $config_path = '',
                                bool $versioning = true)
    {

        // If configuration file was provided
        if (!empty($config_path)) {
            $this->loadConfigurationFile($config_path);
        }

        // Should minified files be versioned?
        $this->versioning = $versioning;
    }

    /**
     * Load library configuration YAML from file $config_file.
     *
     * @param   string  $config_file    Absolute configuration file path.
     */
    public function loadConfigurationFile(string $config_file): array
    {

        // If configuration file doesn't exists, throw exception
        if (!file_exists($config_file)) {
            throw new Exception("AssetsCompressor configuration file {$config_file} doesn't exists.");
        }

        // Load configuration and store it as array
        $this->path_root = pathinfo($config_file, PATHINFO_DIRNAME);
        $this->config    = Yaml::parse(file_get_contents($config_file));
    }

    /**
     * Creates new entry point.
     *
     * @param   array   $output_file    Entry point (output file path)
     * @param   array   $patterns       File patterns (glob() patterns)
     */
    public function addEntryPoint(array $output_file, array $patterns)
    {
        $this->config[$output_file] = $patterns;
    }

    /**
     * Change root directory.
     *
     * If youre using configuration file, configuration directory is used as a root.
     * If youre using addEntryPoint() method, set root directory using this method.
     * 
     * @param   string  $path   Absolute file path
     */
    public function setRootDirectory(string $path)
    {
        $this->path_root = $path;
    }

    /**
     * Set the file path where entry points hashes should be stored.
     *
     * @param   string  $path   Absolute file path
     */
    public function setHashesFilePath(string $path)
    {
        $this->path_hashes = $path;
    }

    /**
     * Run assets compressor
     */
    public function run()
    {

        // If there are no files in configuration, throw exception
        if (empty($this->config)) {
            throw new Exception('No configuration provided, add a configuration file or entry points directly.');
        }

        // If root directory wasn't provided, throw exception
        if (empty($this->path_root)) {
            throw new Exception('The root directory wasn\'t provided.');
        }

        // Process entries
        foreach ($this->config AS $path_output => $patterns) {

            // Pomiń puste sekcje
            if (empty($patterns)) {
                continue;
            }

            // Process single entry point
            if (!is_array($patterns)) {
                $patterns = [$patterns];
            }

            $this->processEntry($path_output, $patterns);
        }

        // If versioning is enabled, store hashes
        if ($this->versioning AND ! empty($this->hashes)) {

            // If there is no hashes file, place one in root directory
            if (empty($this->path_hashes)) {
                $this->path_hashes = $this->path_root.'/'.'busters.json';
            }

            file_put_contents($this->path_hashes, json_encode($this->hashes));
        }
    }

    /**
     * Process a single entry in configuration file (end point file).
     * Directory format: /assets/vender.js for the absolute path, or assets/vendor.js for relative path
     *
     * @param   string          $output_path    Entry point file path
     * @param   array[string]   $patterns
     */
    protected function processEntry(string $output_path, array $patterns)
    {
        // Extension (file type)
        $ext = strtolower(pathinfo($output_path, PATHINFO_EXTENSION));

        // Prepare end point file
        $include_list = $this->buildFilesList($patterns);
        $buffer       = '';
        foreach ($include_list AS $file) {
            $buffer .= file_get_contents($file).($ext === 'js' ? ';' : '').PHP_EOL;
        }

        // Process the buffer
        if (!empty($buffer)) {

            // Save uncompressed version
            $path_uncompressed = $this->path_root.$output_path;
            file_put_contents($path_uncompressed, $buffer);

            // Minify file name
            $dir           = pathinfo($path_uncompressed, PATHINFO_DIRNAME);
            $filename      = pathinfo($path_uncompressed, PATHINFO_FILENAME);
            $path_minified = $dir.'/'.$filename.'.min.';

            // If file shuld be versioned, add a hash
            if ($this->versioning) {
                $hash                             = hash('crc32b', $buffer);
                $path_minified                    .= $hash.'.';
                $this->hashes[$path_uncompressed] = $hash;
            }

            // Add file extension
            $path_minified .= $ext;

            // Process CSS
            if ($ext === 'css') {

                // Create compressor instance
                $compressor = new CSSProcessor;
                $compressor->setLineBreakPosition(1000);
                $compressor->setMemoryLimit('256M');
                $compressor->setMaxExecutionTime(120);
                $compressor->setPcreBacktrackLimit(3000000);
                $compressor->setPcreRecursionLimit(150000);

                // Compress CSS and save the output
                $output_css = $compressor->run($buffer);
                file_put_contents($path_minified, $output_css);

                // Process JS
            } elseif ($ext === 'js') {

                // Compress JS file and save to file
                (new JSProcessor($path_uncompressed))->minify($path_minified);
            }
        }
    }

    /**
     * Build files lisst from provided patterns.
     *
     * @param   array   $patterns   Files patterns
     *
     * @return  array
     */
    protected function buildFilesList(array $patterns): array
    {
        $files        = [];
        $exclude_list = [];

        // Process each pattern
        foreach ($patterns AS $pattern) {

            // If this is the exclude pattern
            if (substr($pattern, 0, 1) === '!') {

                // Filter only files, then add to exclude list
                $path         = glob($this->path_root.substr($pattern, 1));
                $excludes     = array_filter($path, 'is_file');
                $exclude_list = array_replace($exclude_list, $excludes);

                // If this is the include patern
            } else {

                // Find files from include pattern
                $path     = glob($this->path_root.$pattern);
                $includes = array_filter($path, 'is_file');
                $files    = array_replace($files, $includes);
            }
        }

        // Prepare files list and remove duplicates
        $files = array_diff($files, $exclude_list);

        return $files;
    }
}