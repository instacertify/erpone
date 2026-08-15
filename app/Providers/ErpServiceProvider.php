<?php

namespace App\Providers;

use App\Services\Export\BulkExcelExporter;
use App\Services\Gst\GstApiClient;
use App\Services\Settings\ErpSettings;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

class ErpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('erp.php'), 'erp');

        $this->app->singleton(ModuleRegistry::class);
        $this->app->singleton(ErpSettings::class);
        $this->app->singleton(BulkExcelExporter::class);
        $this->app->singleton(GstApiClient::class);
    }

    public function boot(): void
    {
        //
    }
}
