<?php

namespace Grezlikowski\PageSpeed\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'page-speed:install';

    protected $description = 'Install the PageSpeed with History package';

    public function handle(): void
    {
        $this->comment('Publishing PageSpeed configuration...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'page-speed-config',
        ]);

        $this->comment('Publishing PageSpeed migrations...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'page-speed-migrations',
        ]);

        $this->info('PageSpeed with History installed successfully.');
    }
}
