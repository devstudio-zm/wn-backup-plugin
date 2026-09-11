<?php namespace MEB\Backup;

use Storage;
use Sabre\DAV\Client;
use League\Flysystem\Filesystem;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\FilesystemAdapter as LaravelFilesystemAdapter;

class WebDAVAdapterExt extends WebDAVAdapter {

    public $fsConfig;

    public function __construct($client, $fsConfig)
    {
        $this->fsConfig = $fsConfig;
        parent::__construct($client, $fsConfig['path_prefix'] ?? '');
    }

    public function getUrl($path)
    {
        if (!empty($this->fsConfig['path_alias'])) {
            // with this feature you can use symlink to folder
            return $this->fsConfig['baseUri'] . $this->fsConfig['path_alias'] . $path;
        } else {
            return $this->fsConfig['baseUri'] . $this->fsConfig['path_prefix'] . $path;
        }
    }

    // Note: the v1-era deleteDir() override was dropped. Flysystem 3's
    // WebDAVAdapter::deleteDirectory() already treats a 404 as success,
    // which is the behavior this override used to provide.
}

class WebdavServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('webdav', function ($app, $config) {
            $client = new Client($config);
            $adapter = new WebDAVAdapterExt($client, $config);

            return new LaravelFilesystemAdapter(new Filesystem($adapter), $adapter, $config);
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
