<?php

namespace App\Console\Commands;

use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:stocks {dateFrom} {dateTo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch stocks from API';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateFrom = $this->argument('dateFrom');
        $dateTo = $this->argument('dateTo');
        $apiIp = config('app.external_api_ip');
        $apiPort = config('app.external_api_port');
        $apiKey = config('app.external_api_key');

        $apiUrl = "http://$apiIp:$apiPort/api/stocks";
        $page = 1;
        $limit_rows = 500;

        $this->info("Start fetching stocks from API with dates from $dateFrom to $dateTo");

        $data = Http::get($apiUrl, [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'limit' => $limit_rows,
            'key' => $apiKey,
        ]);

        if ($data->failed()) {
            $this->info("\n" . $data->body());
            return;
        }

        $totalPages = $data['meta']['last_page'];

        $stocks = $data['data'] ?? [];

        do {
            $data = Http::get($apiUrl, [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'page' => $page,
                'limit' => $limit_rows,
                'key' => $apiKey,
            ]);

            if ($data->failed()) {
                $this->error('Error fetching orders from API :(');
                return;
            }

            $stocks = $data['data'] ?? [];

            foreach ($stocks as $stock) {
                Stock::create([
                    'date' => $stock['date'],
                    'last_change_date' => $stock['last_change_date'],
                    'supplier_article' => $stock['supplier_article'],
                    'tech_size' => $stock['tech_size'],
                    'barcode' => $stock['barcode'],
                    'is_supply' => $stock['is_supply'],
                    'is_realization' => $stock['is_realization'],
                    'quantity_full' => $stock['quantity_full'],
                    'warehouse_name' => $stock['warehouse_name'],
                    'in_way_to_client' => $stock['in_way_to_client'],
                    'in_way_from_client' => $stock['in_way_from_client'],
                    'nm_id' => $stock['nm_id'],
                    'subject' => $stock['subject'],
                    'category' => $stock['category'],
                    'brand' => $stock['brand'],
                    'sc_code' => $stock['sc_code'],
                    'price' => $stock['price'],
                    'discount' => $stock['discount'],
                ]);
            }
            $this->info("Page $page of $totalPages stocks fetched");

            $page++;
        }
        while (count($stocks) === $limit_rows);

        $this->info("Fetched successfully");
    }
}
