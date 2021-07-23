<?php

namespace Dockworker\Robo\Plugin\Commands;

use Dockworker\Robo\Plugin\Commands\DockworkerLocalCommands;

/**
 * Defines the commands used to interact with a local Jekyll application.
 */
class JekyllLocalCommands extends DockworkerLocalCommands {

  const JEKYLL_CONTAINER_USER_ID = '1000';
  const JEKYLL_BUILDER_STARTING_MESSAGE = 'The local development HTML builder is likely still starting. Please refresh this page in a moment...';

  protected string $jekyllBuilderPath;
  protected string $jekyllBuilderSource;
  protected string $jekyllVolumePath;
  protected string $jekyllIndexFile;
  protected string $curUserGid;

  /**
   * Ensures the builder is up to date.
   *
   * @command local:jekyll:update-builder
   */
  public function setUpLocalBuilder() {
    $this->say("Setting Up Latest Builder...");
    $this->curUserGid = posix_getgid();
    $this->jekyllBuilderPath = $this->repoRoot . "/builder";
    $this->jekyllBuilderSource = $this->repoRoot . "/vendor/unb-libraries/dockworker-jekyll/data/builder/Dockerfile";
    $this->taskExec('mkdir -p')
      ->arg($this->jekyllBuilderPath)
      ->run();
    $this->_copy($this->jekyllBuilderSource, "$this->jekyllBuilderPath/Dockerfile");
  }

  /**
   * Ensures there is a local HTML volume directory and it is as expected.
   *
   * @hook pre-command local:start
   */
  public function setUpLocalHtmlVolumeDirectory() {
    $this->say("Setting Jekyll Volume Permissions...");
    $this->curUserGid = posix_getgid();
    $this->jekyllVolumePath = $this->repoRoot . "/.html";
    $this->jekyllIndexFile = "$this->jekyllVolumePath/index.html";

    $this->setJekyllVolumePermissions();
    $this->setJekyllDefaultIndexFile();
  }

  /**
   * Sets the proper permissions for Jekyll to write to the HTML volume.
   */
  protected function setJekyllVolumePermissions() {
    $this->taskExec('sudo mkdir -p')
      ->arg($this->jekyllVolumePath)
      ->run();
    $this->taskExec('sudo chown')
      ->arg(self::JEKYLL_CONTAINER_USER_ID . ":$this->curUserGid")
      ->arg('-R')
      ->arg($this->jekyllVolumePath)
      ->run();
    $this->taskExec('sudo chmod')
      ->arg('g+w')
      ->arg('-R')
      ->arg($this->jekyllVolumePath)
      ->run();
  }

  /**
   * Adds an index file for nginx to read if the builder is still deploying.
   */
  protected function setJekyllDefaultIndexFile() {
    if (!file_exists($this->jekyllIndexFile)) {
      file_put_contents($this->jekyllIndexFile, self::JEKYLL_BUILDER_STARTING_MESSAGE);
    }
    $this->taskExec('sudo chown')
      ->arg(self::JEKYLL_CONTAINER_USER_ID . ":$this->curUserGid")
      ->arg($this->jekyllIndexFile)
      ->run();
  }

  /**
   * Display local application container logs and monitor for new ones.
   *
   * @param string[] $opts
   *   An array of options to pass to the builder.
   *
   * @option bool $timestamps
   *   Display a timestamp for each line of the logs.
   *
   * @command local:logs:tail
   * @aliases logs
   * @throws \Exception
   *
   * @usage local:logs:tail
   *
   * @return \Robo\Result
   *   The result of the command.
   */
  public function tailLocalLogs(array $opts = ['timestamps' => FALSE]) {
    $opts['all'] = TRUE;
    return parent::tailLocalLogs($opts);
  }

}

