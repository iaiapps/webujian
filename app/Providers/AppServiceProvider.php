<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');
        
        // Macro untuk format tanggal Indonesia
        Carbon::macro('formatIndo', function ($format = 'long') {
            $bulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            
            $hari = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
            ];
            
            switch ($format) {
                case 'short':
                    return $this->format('d') . ' ' . substr($bulan[$this->month], 0, 3) . ' ' . $this->format('Y');
                case 'long':
                    return $this->format('d') . ' ' . $bulan[$this->month] . ' ' . $this->format('Y');
                case 'full':
                    return $hari[$this->format('l')] . ', ' . $this->format('d') . ' ' . $bulan[$this->month] . ' ' . $this->format('Y');
                case 'datetime':
                    return $this->format('d') . ' ' . $bulan[$this->month] . ' ' . $this->format('Y') . ' ' . $this->format('H:i') . ' WIB';
                case 'time':
                    return $this->format('H:i') . ' WIB';
                default:
                    return $this->format($format);
            }
        });
    }
}
