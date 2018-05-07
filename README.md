# AssetsCompressor
Combine, compress and version CSS/JS code.

## Command line examples

### Execute AssetsCompressor using default configuraton file
Running `assets-compressor` alone from command line will execute application
using `assets.yml` or `.assets.yml` configuration file located in project root.
```bash
assets-compressor
```

### Sample configuration file
Configuration is stored in yml file. When using command line application will search
for it in `/assets.yml` and `/.assets.yml`. First level entries are entry points (output files),
second level (intended entries) are input files.
```yml
# /.assets.yml
/assets/css/vendor.css
    - /vendor/select2/select2/dist/select2.css
    - /vendor/jquery-ui/jquery-ui/css/jquery-ui.css
    - /resources/components/bootstrap4/dist/css/boostrap.css
/assets/css/main.css
    - /resources/components/css/*.css
# Ignore somelibrary.css
    - !/resources/components/css/somelibrary.css
/assets/js/vendor.js
    - /vendor/select2/select2/dist/select2.js
    - /vendor/jquery-ui/jquery-ui/css/jquery-ui.js
    - /resources/components/bootstrap4/dist/js/boostrap.js
/assets/js/app.js
    - /resources/components/js/*.js
# Ignore somelibrary.js
    - !/resources/components/js/somelibrary.js
```

### Files hashes
By default application generates `busters.json` file that contains file hashes map 
in project root directory (it is a configuration file directory by default). If you 
want to change where hashes file is stored provide hashes directory using: 
`AssetsCompressor->setHashesFilePath('/asssets.json')`.

### Using class
You can also use the class directly. 

#### Simple execution using config file
```php
<?php
use AssetsCompressor\AssetsCompressor;

$compressor = new AssetsCompressor('/PATH/TO/CONFIG/assets.yml');
$compressor->run();

```

#### Adding entry points manualy
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