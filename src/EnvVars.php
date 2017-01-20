<?php

namespace FourKitchens\ProjectBase;

use Symfony\Component\Yaml\Yaml;

/**
 * Handles environment variables defined in project.yml.
 */
class EnvVars {
  protected $yaml;

  public function __construct(
    $yaml = new Yaml();
  )

  public function getProjectConfig() {
    $projectConfig = [];

    try {
      $projectConfig = $this->yaml->parse(file_get_contents('../project.yml'));
    }
    catch (Exception $e) {}

    // Get local project config from local.project.yml and merge.
    try {
      $local_config = $this->yaml->parse(file_get_contents('../local.project.yml'));
      $projectConfig = array_replace_recursive($projectConfig, $local_config);
    }
    catch (Exception $e) {}

    return $projectConfig;
  }

  public function setEnvVars() {
    $projectConfig = $this->getProjectConfig();

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
