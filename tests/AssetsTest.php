<?php

use CRM\Assets,
    PHPUnit\Framework\TestCase;

/**
 * @testdox Obsługa plików statycznych
 * @coversDefaultClass \CRM\Assets
 */
final class AssetsTest extends TestCase
{

    /**
     * @testdox Generowanie URL pliku z hashem
     * @covers ::url()
     */
    public function testUrl()
    {
        
        // Utwórz plik JS do testów
        $url = '/tests/Unit/CRM/AssetsTest.js';
        $path = PATH_ROOT.$url;
        file_put_contents($path, sha1(rand()));

        // Wygeneruj 2 razy URL dla porównania
        $url1 = Assets::url($url);
        $url2 = Assets::url($url);

        // Oba adresy powinny być takie same bo treść się nie zmieniła
        $this->assertEquals($url1, $url2, 'Dwa wygenerowania URL z tej samej treści są takie same');

        // Zmodyfikuj plik JS do testów
        file_put_contents($path, sha1(rand()));

        // Wygeneruj URL dla porównania
        $url3 = Assets::url($url);

        // Sprawdź czy adres się zmienił (zmieniona została treść, adresy powinien być nowy)
        $this->assertNotEquals($url1, $url3, 'Zmieniła się treść pliku, zmienił się adres');

        // Usuń testowy plik
        unlink($path);
    }
}