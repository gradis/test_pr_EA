<?php

namespace App\Console\Commands;

use App\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:sales {dateFrom} {dateTo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch sales from API';
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

        $apiUrl = "http://$apiIp:$apiPort/api/sales";
        $page = 1;
        $limit_rows = 500;

        $this->info("Start fetching sales from API with dates from $dateFrom to $dateTo");

        $data = Http::get($apiUrl, [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'limit' => $limit_rows,
            'key' => $apiKey,
        ]);

        if ($data->failed()) {
            $this->error('Error fetching sales from API :(');
            return;
        }

        $totalPages = $data['meta']['last_page'];

        $sales = $data['data'] ?? [];

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

            $sales = $data['data'] ?? [];

            foreach ($sales as $sale) {
                Sale::create(
                    [
                        'g_number' => $sale['g_number'],
                        'date' => $sale['date'],
                        'last_change_date' => $sale['last_change_date'],
                        'supplier_article' => $sale['supplier_article'],
                        'tech_size' => $sale['tech_size'],
                        'barcode' => $sale['barcode'],
                        'total_price' => $sale['total_price'],
                        'discount_percent' => $sale['discount_percent'],
                        'is_supply' => $sale['is_supply'],
                        'is_realization' => $sale['is_realization'],
                        'promo_code_discount' => $sale['promo_code_discount'],
                        'warehouse_name' => $sale['warehouse_name'],
                        'country_name' => $sale['country_name'],
                        'oblast_okrug_name' => $sale['oblast_okrug_name'],
                        'region_name' => $sale['region_name'],
                        'income_id' => $sale['income_id'],
                        'sale_id' => $sale['sale_id'],
                        'odid' => $sale['odid'],
                        'spp' => $sale['spp'],
                        'for_pay' => $sale['for_pay'],
                        'finished_price' => $sale['finished_price'],
                        'price_with_disc' => $sale['price_with_disc'],
                        'nm_id' => $sale['nm_id'],
                        'subject' => $sale['subject'],
                        'category' => $sale['category'],
                        'brand' => $sale['brand'],
                        'is_storno' => $sale['is_storno'],
                    ]);
            }
            $this->info("Page $page of $totalPages sales fetched");

            $page++;
        }
        while (count($sales) === $limit_rows);

        $this->info("Fetched successfully");
    }
}
