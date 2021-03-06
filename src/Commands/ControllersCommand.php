<?php

namespace NCH\Codeforlife\Commands;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class ControllersCommand extends Command
{
    use AppNamespaceDetectorTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'codeforlife:controllers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all the controllers from Codeforlife.';

    /**
     * The Filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Filename of stub-file.
     *
     * @var string
     */
    protected $stub = 'controller.stub';

    /**
     * Create a new command instance.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $stub = $this->getStub();
        $files = $this->filesystem->files(base_path('vendor/nch/codeforlife/src/Http/Controllers'));
        $namespace = config('codeforlife.controllers.namespace', 'NCH\\Codeforlife\\Http\\Controllers');

        $appNamespace = $this->getAppNamespace();

        if (!starts_with($namespace, $appNamespace)) {
            return $this->error('The controllers namespace start must the your application namespace.');
        }

        $location = str_replace('\\', '/', substr($namespace, strlen($appNamespace)));

        if (!$this->filesystem->isDirectory(app_path($location))) {
            $this->filesystem->makeDirectory(app_path($location));
        }

        foreach ($files as $file) {
            $parts = explode('/', $file);
            $filename = end($parts);

            if ($filename == 'Controller.php') {
                continue;
            }

            $path = app_path($location.'/'.$filename);

            if (!$this->filesystem->exists($path) or $this->option('force')) {
                $class = substr($filename, 0, strpos($filename, '.'));
                $content = $this->generateContent($stub, $class);
                $this->filesystem->put($path, $content);
            }
        }

        $this->info('Published Codeforlife controllers!');
    }

    /**
     * Get stub content.
     *
     * @return string
     */
    public function getStub()
    {
        return $this->filesystem->get(base_path('/vendor/nch/codeforlife/stubs/'.$this->stub));
    }

    /**
     * Generate real content from stub.
     *
     * @param $stub
     * @param $class
     *
     * @return mixed
     */
    protected function generateContent($stub, $class)
    {
        $namespace = config('codeforlife.controllers.namespace', 'NCH\\Codeforlife\\Http\\Controllers');

        $content = str_replace(
            'DummyNamespace',
            $namespace,
            $stub
        );

        $content = str_replace(
            'FullBaseDummyClass',
            'NCH\\Codeforlife\\Http\\Controllers\\'.$class,
            $content
        );

        $content = str_replace(
            'BaseDummyClass',
            'Base'.$class,
            $content
        );

        $content = str_replace(
            'DummyClass',
            $class,
            $content
        );

        return $content;
    }

    /**
     * Get command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing controller files'],
        ];
    }
}
