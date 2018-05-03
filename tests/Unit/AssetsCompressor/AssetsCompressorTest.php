<?php

namespace AssetsCompressor\Tests\Unit\AssetsCompressor;

use AssetsCompressor\AssetsCompressor;
use PHPUnit\Framework\TestCase;

/**
 * @testdox AssetsCompressor\AssetsCompressor
 * @coversDefaultClass AssetsCompressor
 */
final class AssetsCompressorTest extends TestCase
{

    /**
     * @testdox AssetsCompressor\AssetsCompressor
     * @covers ::run()
     */
    public function testRun()
    {

        // Run compressor
        $compressor = new AssetsCompressor(__DIR__.'/AssetsCompressorTest.yml');
        $compressor->run();

        // Output directory
        $root = __DIR__.'/.output';

        // Check results
        $this->assertFileExists($root.'/script.js', 'Deweloperski plik JS istnieje.');
        $this->assertFileExists($root.'/script.min.js', 'Produkcyjny plik JS istnieje.');
        $this->assertFileExists($root.'/stylesheet.css', 'Deweloperski plik CSS istnieje.');
        $this->assertFileExists($root.'/stylesheet.min.css', 'Produkcyjny plik CSS istnieje.');
    }

    /**
     * Clear up tests directory
     */
    public static function tearDownAfterClass() {

        parent::tearDownAfterClass();

        // Output root
        $root = __DIR__.'/.output';

        // Remove output files
        $list = glob($root.'/*');
        foreach( $list AS $file ) {
            unlink($file);
        }

    }

}