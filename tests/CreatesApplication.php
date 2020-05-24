<?php

namespace Tests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        /**
         * 可以存在 .env.test文件，就加載 .env.test的環境
         */
        if (file_exists(dirname(__DIR__) . '/.env.test')) {
            (new \Dotenv\Dotenv(dirname(__DIR__), '.env.test'))->load();
        }

        $app->make(Kernel::class)->bootstrap();

        Hash::setRounds(4);

        return $app;
    }
}
