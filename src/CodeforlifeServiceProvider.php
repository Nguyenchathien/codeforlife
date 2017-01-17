<?php

namespace NCH\Codeforlife;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageServiceProvider;
use NCH\Codeforlife\Facades\Codeforlife as CodeforlifeFacade;
use NCH\Codeforlife\Http\Middleware\CodeforlifeAdminMiddleware;
use NCH\Codeforlife\Models\Menu;
use NCH\Codeforlife\Models\User;

class CodeforlifeServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->register(ImageServiceProvider::class);

        $loader = AliasLoader::getInstance();
        $loader->alias('Menu', Menu::class);
        $loader->alias('Codeforlife', CodeforlifeFacade::class);

        $this->app->singleton('codeforlife', function () {
            return new Codeforlife();
        });

        $this->registerViewComposers();
        $this->registerAlertComponents();

        if ($this->app->runningInConsole()) {
            $this->registerPublishableResources();
            $this->registerConsoleCommands();
        } else {
            $this->registerAppCommands();
        }
    }

    /**
     * Bootstrap the application services.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function boot(Router $router, Dispatcher $event)
    {
        if (config('codeforlife.user.add_default_role_on_register')) {
            $app_user = config('codeforlife.user.namespace');
            $app_user::created(function ($user) {
                if (is_null($user->role_id)) {
                    User::findOrFail($user->id)
                        ->setRole(config('codeforlife.user.default_role'))
                        ->save();
                }
            });
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'codeforlife');

        $router->middleware('admin.user', CodeforlifeAdminMiddleware::class);

        $event->listen('codeforlife.alerts.collecting', function () {
            $this->addStorageSymlinkAlert();
        });
    }

    /**
     * Register view composers.
     */
    protected function registerViewComposers()
    {
        // Register alerts
        View::composer('codeforlife::index', function ($view) {
            $view->with('alerts', CodeforlifeFacade::alerts());
        });
    }

    /**
     * Add storage symlink alert.
     */
    protected function addStorageSymlinkAlert()
    {
        $currentRouteAction = app('router')->current()->getAction();
        $routeName = is_array($currentRouteAction) ? array_get($currentRouteAction, 'as') : null;

        if ($routeName == 'codeforlife.dashboard' && request()->has('fix-missing-storage-symlink') && !file_exists(public_path('storage'))) {
            $this->fixMissingStorageSymlink();
        } elseif (!file_exists(public_path('storage'))) {
            $alert = (new Alert('missing-storage-symlink', 'warning'))
                ->title('Missing storage symlink')
                ->text('We could not find a storage symlink. This could cause problems with loading media files from the browser.')
                ->button('Fix it', '?fix-missing-storage-symlink=1');

            CodeforlifeFacade::addAlert($alert);
        }
    }

    protected function fixMissingStorageSymlink()
    {
        app('files')->link(storage_path('app/public'), public_path('storage'));

        if (file_exists(public_path('storage'))) {
            $alert = (new Alert('fixed-missing-storage-symlink', 'success'))
                ->title('Missing storage symlink created')
                ->text('We just created the missing symlink for you.');
        } else {
            $alert = (new Alert('failed-fixing-missing-storage-symlink', 'danger'))
                ->title('Could not create missing storage symlink')
                ->text('We failed to generate the missing symlink for your application. It seems like your hosting provider does not support it.');
        }

        CodeforlifeFacade::addAlert($alert);
    }

    /**
     * Register alert components.
     */
    protected function registerAlertComponents()
    {
        $components = ['title', 'text', 'button'];

        foreach ($components as $component) {
            $class = 'NCH\\Codeforlife\\Alert\\Components\\'.ucfirst(camel_case($component)).'Component';

            $this->app->bind("codeforlife.alert.components.{$component}", $class);
        }
    }

    /**
     * Register the publishable files.
     */
    private function registerPublishableResources()
    {
        $basePath = dirname(__DIR__);
        $publishable = [
            'codeforlife_assets' => [
                "$basePath/publishable/assets" => public_path('vendor/nch/codeforlife/assets'),
            ],
            'migrations' => [
                "$basePath/publishable/database/migrations/" => database_path('migrations'),
            ],
            'seeds' => [
                "$basePath/publishable/database/seeds/" => database_path('seeds'),
            ],
            'demo_content' => [
                "$basePath/publishable/demo_content/" => storage_path('app/public'),
            ],
            'config' => [
                "$basePath/publishable/config/codeforlife.php" => config_path('codeforlife.php'),
            ],
            'views' => [
                "$basePath/publishable/views/" => resource_path('views/vendor/codeforlife'),
            ],
        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }

    /**
     * Register the commands accessible from the Console.
     */
    private function registerConsoleCommands()
    {
        $this->commands(Commands\InstallCommand::class);
        $this->commands(Commands\ControllersCommand::class);
        $this->commands(Commands\AdminCommand::class);
    }

    /**
     * Register the commands accessible from the App.
     */
    private function registerAppCommands()
    {
        $this->commands(Commands\MakeModelCommand::class);
    }
}
