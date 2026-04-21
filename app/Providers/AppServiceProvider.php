<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
        Blade::directive('durSec', function ($s) {
            return "<?php echo intdiv($s, 3600).'h '.str_pad(intdiv($s % 3600, 60),2,'0',STR_PAD_LEFT).'m '. str_pad($s % 60,2,'0',STR_PAD_LEFT).'s'; ?>";
        });
    }
}
