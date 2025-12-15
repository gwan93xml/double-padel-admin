<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Barryvdh\DomPDF\Facade\Pdf;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        URL::forceHttps();
        // setlocale(LC_TIME, 'id_ID');
        // \Carbon\Carbon::setLocale('id');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register Tahoma font for DomPDF
        Pdf::setOptions([
            'font_dir' => storage_path('fonts'),
            'font_cache' => storage_path('fonts'),
            'enable_font_subsetting' => true,
        ]);

        // Register font using DomPDF instance
        $this->registerDomPDFFonts();
    }

    private function registerDomPDFFonts()
    {
        try {
            $dompdf = Pdf::getDomPDF();
            if ($dompdf) {
                $fontMetrics = $dompdf->getFontMetrics();

                // Register Tahoma font
                $fontMetrics->registerFont(
                    ['family' => 'tahoma', 'style' => 'normal', 'weight' => 'normal'],
                    storage_path('fonts/tahoma.ttf')
                );

                $fontMetrics->registerFont(
                    ['family' => 'tahoma', 'style' => 'normal', 'weight' => 'bold'],
                    storage_path('fonts/tahoma-bold.ttf')
                );
            }
        } catch (\Exception $e) {
            // Log error if font registration fails
            Log::warning('Failed to register Tahoma font: ' . $e->getMessage());
        }
    }
}
