<?php

namespace tests\Unit\AssetsCompressor;

use AssetsCompressor\AssetsCompressor;
use PHPUnit\Framework\TestCase;

/**
 * @testdox \AssetsCompressor\AssetsCompressor
 * @covers \AssetsCompressor\AssetsCompressor
 */
final class AssetsCompressorTest extends TestCase
{

    /**
     * @testdox Compresion
     * @covers \AssetsCompressor\AssetsCompressor::run
     */
    public function testRun()
    {
        // Run compressor
        $compressor = new AssetsCompressor(__DIR__.'/AssetsCompressorTest.yml');
        $compressor->run();

        // Output directory
        $root = __DIR__.'/.output';

        // Check results
        $this->assertFileExists(__DIR__.'/busters.json', 'Hashes file exists.');

        // Load hashes
        $buff            = file_get_contents(__DIR__.'/busters.json');
        $hash            = (array) json_decode($buff);
        $script_hash     = $hash['/.output/script.js'];
        $stylesheet_hash = $hash['/.output/stylesheet.css'];

        // Check javascript files
        $this->assertFileExists($root.'/script.js',
            'Uncompressed JS file exists.');
        $this->assertFileExists($root.'/script.min.'.$script_hash.'.js',
            'Compressed and hashed JS file exists.');

        // Check stylesheet files
        $this->assertFileExists($root.'/stylesheet.css',
            'Uncompressed CSS file exists.');
        $this->assertFileExists($root.'/stylesheet.min.'.$stylesheet_hash.'.css',
            'Compressed and hashed CSS file exists.');
    }

    /**
     * @testdox Output files validity
     * @depends testRun
     * @coversNothing
     */
    public function testValiditiy()
    {

        // Load hashes
        $buff            = file_get_contents(__DIR__.'/busters.json');
        $hash            = (array) json_decode($buff);
        $script_hash     = $hash['/.output/script.js'];
        $stylesheet_hash = $hash['/.output/stylesheet.css'];
        $root            = __DIR__.'/.output';

        // Check uncompressed JS
        $this->assertContains('\'AASDAIU9AS767A\'',
            file_get_contents($root.'/script.js'),
            'Combined contents contain scriptA content');
        $this->assertContains('\'B0720FGN7A7F\'',
            file_get_contents($root.'/script.js'),
            'Combined contents contain scriptB content');

        // Check compressed JS
        $this->assertContains('\'AASDAIU9AS767A\'',
            file_get_contents($root.'/script.min.'.$script_hash.'.js'),
            'Compressed contents contain scriptA content');
        $this->assertContains('\'B0720FGN7A7F\'',
            file_get_contents($root.'/script.min.'.$script_hash.'.js'),
            'Compressed contents contain scriptB content');

        // Check uncompressed CSS
        $this->assertContains('root>A',
            file_get_contents($root.'/stylesheet.css'),
            'Combined contents contain stylesheetA content');
        $this->assertContains('root>B',
            file_get_contents($root.'/stylesheet.css'),
            'Combined contents contain stylesheetB content');
        $this->assertNotContains('root>C',
            file_get_contents($root.'/stylesheet.css'),
            'Combined contents doesn\'t contain stylesheetC content');

        // Check compressed CSS
        $this->assertContains('root>A',
            file_get_contents($root.'/stylesheet.min.'.$stylesheet_hash.'.css'),
            'Compressed contents container stylesheetA content');
        $this->assertContains('root>B',
            file_get_contents($root.'/stylesheet.min.'.$stylesheet_hash.'.css'),
            'Compressed contents container stylesheetB content');
        $this->assertNotContains('root>C',
            file_get_contents($root.'/stylesheet.min.'.$stylesheet_hash.'.css'),
            'Combined contents doesn\'t contain stylesheetC content');
    }

    /**
     * Cleanup tests directory
     */
    public static function tearDownAfterClass()
    {

        parent::tearDownAfterClass();

        // Output root
        $root = __DIR__.'/.output';

        // Remove output files
        $list = array_merge(glob($root.'/*'), glob(__DIR__.'/busters.json'));
        foreach ($list AS $file) {
            unlink($file);
        }
    }
}