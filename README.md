# AssetsCompressor
Combine, compress and version CSS/JS code.

## Command line examples

### Execute AssetsCompressor using default configuraton file
Running `assets-compressor` alone from command line will execute application
using `assets.yml` or `.assets.yml` configuration file located in project root.
```bash
assets-compressor
```

### Using class
You can also use the class directly. 

#### Simple execution using config file
```php
<?php
use AssetsCompressor\AssetsCompressor;

$compressor = new AssetsCompressor('/PATH/TO/CONFIG');
$compressor->run();

```

#### Adding sections manualy
```php
<?php
use AssetsCompressor\AssetsCompressor;

// Create instance
$compressor = new AssetsCompressor();

// Add entry points (output files)
$compressor->addEntryPoint('/assets/css/vendor.css', [
    '/vendor/select2/dist/select2.css',
    '/vendor/jquery-ui/dist/jquery-ui.css',
    '/css/external/*.css',
]);
$compressor->addEntryPoint('/assets/css/main.css', [
    '/assets/css/components/*.css',
]);

// Execute compressor
$compressor->run();

```