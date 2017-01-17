<?php

namespace NCH\Codeforlife;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use NCH\Codeforlife\Models\Permission;
use NCH\Codeforlife\Models\Setting;
use NCH\Codeforlife\Models\User;

class Codeforlife
{
    private static $instance;

    protected $version;
    protected $filesystem;

    protected $alerts = [];

    protected $allertsCollected = false;

    public function __construct()
    {
        $this->filesystem = app(Filesystem::class);

        $this->findVersion();
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function setting($key, $default = null)
    {
        $setting = Setting::where('key', '=', $key)->first();
        if (isset($setting->id)) {
            return $setting->value;
        }

        return $default;
    }

    public static function image($file, $default = '')
    {
        if (!empty($file) && Storage::exists(config('codeforlife.storage.subfolder').$file)) {
            return Storage::url(config('codeforlife.storage.subfolder').$file);
        }

        return $default;
    }

    public static function routes()
    {
        require __DIR__.'/../routes/codeforlife.php';
    }

    public static function can($permission)
    {
        // Check if permission exist
        $exist = Permission::where('key', $permission)->first();

        if ($exist) {
            $user = User::find(Auth::id());
            if ($user == null) {
                throw new UnauthorizedHttpException(null);
            }
            if (!$user->hasPermission($permission)) {
                throw new UnauthorizedHttpException(null);
            }
        }
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function addAlert(Alert $alert)
    {
        $this->alerts[] = $alert;
    }

    public function alerts()
    {
        if (!$this->allertsCollected) {
            event('codeforlife.alerts.collecting');

            $this->allertsCollected = true;
        }

        return $this->alerts;
    }

    protected function findVersion()
    {
        if (!is_null($this->version)) {
            return;
        }

        if ($this->filesystem->exists(base_path('composer.lock'))) {
            // Get the composer.lock file
            $file = json_decode(
                $this->filesystem->get(base_path('composer.lock'))
            );

            // Loop through all the packages and get the version of codeforlife
            foreach ($file->packages as $package) {
                if ($package->name == 'nch/codeforlife') {
                    $this->version = $package->version;
                    break;
                }
            }
        }
    }
}
