<?php

namespace NCH\Codeforlife\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Intervention\Image\ImageServiceProviderLaravel5;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use NCH\Codeforlife\Traits\Seedable;
use NCH\Codeforlife\CodeforlifeServiceProvider;

class InstallCommand extends Command
{
    use Seedable;

    protected $seedersPath = __DIR__.'/../../publishable/database/seeds/';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'codeforlife:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Codeforlife Admin package';

    protected function getOptions()
    {
        return [
            ['with-dummy', null, InputOption::VALUE_NONE, 'Install with dummy data', null],
        ];
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" '.getcwd().'/composer.phar';
        }

        return 'composer';
    }

    /**
     * Execute the console command.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     *
     * @return void
     */
    public function fire(Filesystem $filesystem)
    {
        $this->info('Publishing the Codeforlife assets, database, and config files');
        $this->call('vendor:publish', ['--provider' => CodeforlifeServiceProvider::class]);
        $this->call('vendor:publish', ['--provider' => ImageServiceProviderLaravel5::class]);

        $this->info('Migrating the database tables into your application');
        $this->call('migrate');

        $this->info('Dumping the autoloaded files and reloading all new files');

        $composer = $this->findComposer();

        $process = new Process($composer.' dump-autoload');
        $process->setWorkingDirectory(base_path())->run();

        $this->info('Adding Codeforlife routes to routes/web.php');
        $filesystem->append(
            base_path('routes/web.php'),
            "\n\nRoute::group(['prefix' => 'admin'], function () {\n    Codeforlife::routes();\n});\n"
        );

        $this->info('Seeding data into the database');
        $this->seed('CodeforlifeDatabaseSeeder');

        if ($this->option('with-dummy')) {
            $this->seed('CodeforlifeDummyDatabaseSeeder');
        }

        $this->info('Adding the storage symlink to your public folder');
        $this->call('storage:link');

        $this->info('Successfully installed Codeforlife! Enjoy 🎉');
    }
}
