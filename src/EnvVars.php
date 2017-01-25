<?php

namespace FourKitchens\ProjectCore;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Weevers\Path\Path;

/**
 * Handles environment variables defined in project.yml.
 */
class EnvVars {

  /**
   * Return the absolute path to the project root.
   *
   * @return string
   *  Absolute path to the project root.
   */
  protected static function getProjectRoot() {
    $path = new Path();
    return $path->resolve(__FILE__, '../../../../../');
  }

  /**
   * Get project configuration defined in project.yml.
   *
   * @return array
   *   An associative array of project configuration settings.
   */
  public static function getProjectConfig() {
    $yaml = new Yaml();
    $fs = new Filesystem();
    $path = new Path();
    $projectConfig = [];
    $projectRoot = self::getProjectRoot();

    // Parse project.yml.
    try {
      $projectConfig = $yaml->parse(file_get_contents($path->resolve($projectRoot, './project.yml')));
    }
    catch (Exception $e) {}

    // Check if a local.project.yml file exists.
    if ($fs->exists($path->resolve($projectRoot, './local.project.yml'))) {
      // Parse local.project.yml project config from local.project.yml and merge.
      try {
        $local_config = $yaml->parse(file_get_contents($path->resolve($projectRoot, './local.project.yml')));
        $projectConfig = array_replace_recursive($projectConfig, $local_config);
      }
      catch (Exception $e) {
      }
    }

    return $projectConfig;
  }

  /**
   * Set the environment variables defined in project.yml.
   */
  public function setEnvVars() {
    $projectConfig = self::getProjectConfig();

    if (isset($projectConfig['env'])) {
      $defaults = $projectConfig['env']['default'];

      foreach ($projectConfig['env'] as $env => $vars) {
        // Fill in defaults.
        foreach ($defaults as $default_key => $default_value) {
          if (!isset($projectConfig['env'][$env][$default_key])) {
            $projectConfig['env'][$env][$default_key] = $default_value;
          }
        }

        foreach ($projectConfig['env'][$env] as $key => $value) {
          $env_var = 'ENV_' . strtoupper($env) . '_' . strtoupper($key);
          putenv("$env_var=$value");
        }
      }
    }
  }

}
