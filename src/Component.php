<?php

declare(strict_types=1);

namespace Keboola\ExJhCsseCovid19;

use Keboola\Component\BaseComponent;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Component extends BaseComponent
{
    private const REPO_DIR = '/tmp/data/';
    private const CSV_DIR = self::REPO_DIR . 'csse_covid_19_data/csse_covid_19_daily_reports/';
    private const TARGET_DIR = '/data/out/tables/';


    protected function run(): void
    {
        $this->cloneRepository();
        $finder = (new Finder())->in(self::CSV_DIR)->name('*.csv');
        $fs = new Filesystem();
        foreach ($finder as  $file) {
            /** @var \SplFileInfo $file */
            $fs->copy($file->getPathname(), self::TARGET_DIR . $file->getFilename());
            $this->getLogger()->info(sprintf("Found %s", $file->getFilename()));
        }
        return;
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }

    private function cloneRepository(): void
    {

        $this->getLogger()->info('Clonning data from repository');
        (new Filesystem())->remove(self::REPO_DIR);
        $process = new Process([
            'git',
            'clone',
            'https://github.com/CSSEGISandData/COVID-19.git',
            self::REPO_DIR,
        ]);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->getLogger()->info($process->getOutput());
    }
}
