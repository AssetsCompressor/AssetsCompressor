<?php

use AssetsCompressor\AssetsCompressor,
    PHPUnit\Framework\TestCase;

/**
 * @testdox AssetsCompressor\AssetsCompressor
 * @coversDefaultClass AssetsCompressor\AssetsCompressor
 */
final class AssetsCompressorTest extends TestCase
{

    /**
     * @testdox AssetsCompressor\AssetsCompressor
     * @covers ::run()
     */
    public function testRun()
    {

        // Przygotuj instancję kompresora
        $compressor = new AssetsCompressor(__DIR__.'/CompressorTest.yml');
        $compressor->run();

        $this->assertFileExists(__DIR__.'/CompressorTest.js', 'Deweloperski plik CSS istnieje.');
        $this->assertFileExists(__DIR__.'/CompressorTest.min.js', 'Produkcyjny plik CSS istnieje.');
        $this->assertFileExists(__DIR__.'/CompressorTest.css', 'Deweloperski plik CSS istnieje.');
        $this->assertFileExists(__DIR__.'/CompressorTest.min.css', 'Produkcyjny plik CSS istnieje.');
    }

    /**
     * @testdox Check if include/exclude patterns work propertly
     * @covers ::buildFilesList
     */
    public function testBuildFilesList()
    {

        // Patterns to check against
        $patterns = [
            
        ];

        // Reflection to expose protected method
        $method = new ReflectionMethod(AssetsCompressor::class, 'buildFilesList');
        $method->setAccessible(true);

        // Get method result
        $result = $method->invoke(new Foo, $patterns);
    }

    /**
     * Usuń pozostałości testów.
     */
    public function tearDown() {

        // Usuń pliki JS
        if( file_exists(__DIR__.'/CompressorTest.js') ) {
            unlink(__DIR__.'/CompressorTest.js');
        }
        if( file_exists(__DIR__.'/CompressorTest.min.js') ) {
            unlink(__DIR__.'/CompressorTest.min.js');
        }

        // Usuń pliki CSS
        if( file_exists(__DIR__.'/CompressorTest.css') ) {
            unlink(__DIR__.'/CompressorTest.css');
        }
        if( file_exists(__DIR__.'/CompressorTest.min.css') ) {
            unlink(__DIR__.'/CompressorTest.min.css');
        }

        parent::tearDown();
    }

}