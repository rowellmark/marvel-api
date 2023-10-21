<?php

namespace MARVELAPI;

class FileLoader
{
  /**
   * Loads all PHP files in a given directory.
   *
   * @param string $directory_name
   * @access public
   */
  public function load_directory($directory_name) {
    $path = trailingslashit(MARVEL_DIR . $directory_name);
    $file_names = glob($path . '*.php');
    foreach ($file_names as $filename) {
      if (file_exists($filename)) {
        require_once $filename;
      }
    }
  }

  /**
   * Loads specified PHP files from the plugin includes directory.
   *
   * @param array $file_names The names of the files to be loaded in the includes directory.
   * @access public
   */
  public function load_files($file_names = []) {
    foreach ($file_names as $file_name) {
      if (file_exists($path = MARVEL_DIR . $file_name . '.php')) {
        require_once $path;
      }
    }
  }
}
