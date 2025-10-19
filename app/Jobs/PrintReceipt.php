<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Native\Laravel\Facades\System;

class PrintReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $html;
    public $printerName;

    /**
     * Create a new job instance.
     */
    public function __construct(string $html, ?string $printerName = null)
    {
        $this->html        = $html;
        $this->printerName = $printerName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get all available printers
            $printers = System::printers();

            if (empty($printers)) {
                Log::error('No printers found');
                throw new \Exception('Tidak ada printer yang tersedia');
            }

            // If printer name specified, find it
            if ($this->printerName) {
                $printer = collect($printers)->first(function ($p) {
                    return $p->name === $this->printerName
                    || $p->displayName === $this->printerName;
                });

                if (! $printer) {
                    Log::error("Printer not found: {$this->printerName}");
                    throw new \Exception("Printer '{$this->printerName}' tidak ditemukan");
                }

                // Print to specific printer
                System::print($this->html, $printer, [
                    'silent'   => true, // Print tanpa dialog
                    'pageSize' => [
                        'width'  => 80000, // 80mm dalam microns untuk thermal printer
                        'height' => 297000,
                    ],
                    'margins'  => [
                        'top'    => 0,
                        'bottom' => 0,
                        'left'   => 0,
                        'right'  => 0,
                    ],
                ]);
            } else {
                // Print to default printer
                System::print($this->html, null, [
                    'silent'   => true,
                    'pageSize' => [
                        'width'  => 80000,
                        'height' => 297000,
                    ],
                    'margins'  => [
                        'top'    => 0,
                        'bottom' => 0,
                        'left'   => 0,
                        'right'  => 0,
                    ],
                ]);
            }

            Log::info("Print success to: " . ($this->printerName ?? 'default printer'));
        } catch (\Exception $e) {
            Log::error('Print failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('PrintReceipt job failed: ' . $exception->getMessage());
    }
}
