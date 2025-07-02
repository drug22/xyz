<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command
{
    protected $signature = 'exchange-rates:update';
    protected $description = 'Update exchange rates from external API daily';

    public function handle()
    {
        $this->info('Updating exchange rates...');

        $service = new ExchangeRateService();
        $rates = $service->fetchRatesFromECB();

        if (count($rates) > 0) {
            $this->info('Exchange rates updated successfully at ' . now());
            $this->info('Total rates: ' . count($rates));
        } else {
            $this->error('Failed to update exchange rates.');
        }

        return 0;
    }
}
