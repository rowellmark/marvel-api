<?php
/**
 * Plugin Name: Marvel API
 * Description: Pull API OF Marvel
 * Version: 1.0.0
 * Author: Rowell Mark M Blanca
 * Author URI: https://rowellmark.github.io/
 */

namespace MARVELAPI;

define('MARVEL_URL', plugin_dir_url( __FILE__ ));
define('MARVEL_DIR', realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR);
define('MARVEL_RESOURCES', MARVEL_URL . 'resources/');
define('MARVEL_VIEWS', MARVEL_DIR . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);
define('MARVEL_NAME', 'Marvel');
define('MARVEL_SLUG', 'marvel-api');

require 'FileLoader.php';

$fileLoader = new FileLoader();

// Load Core
$fileLoader->load_files(['app/App']);
new App\App(__FILE__);

// Load Files
$fileLoader->load_directory('helpers');
$fileLoader->load_directory('config');
