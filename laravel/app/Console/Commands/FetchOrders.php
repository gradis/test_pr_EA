<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Order;

class FetchOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:orders {dateFrom} {dateTo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch orders from API';

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

        $apiUrl = "http://$apiIp:$apiPort/api/orders";
        $page = 1;
        $limit_rows = 500;

        $this->info("Start fetching orders from API with dates from $dateFrom to $dateTo");

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

        $totalPages = $data['meta']['last_page'];

        $orders = $data['data'] ?? [];

        do {
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

            $orders = $data['data'] ?? [];

            foreach ($orders as $order) {
                Order::create(
                    [
                        'g_number' => $order['g_number'],
                        'date' => $order['date'],
                        'last_change_date' => $order['last_change_date'],
                        'supplier_article' => $order['supplier_article'],
                        'tech_size' => $order['tech_size'],
                        'barcode' => $order['barcode'],
                        'total_price' => $order['total_price'],
                        'discount_percent' => $order['discount_percent'],
                        'warehouse_name' => $order['warehouse_name'],
                        'income_id' => $order['income_id'],
                        'odid' => $order['odid'],
                        'nm_id' => $order['nm_id'],
                        'subject' => $order['subject'],
                        'category' => $order['category'],
                        'brand' => $order['brand'],
                        'is_cancel' => $order['is_cancel'],
                        'cancel_dt' => $order['cancel_dt'],
                    ]);
            }
            $this->info("Page $page of $totalPages orders fetched");

            $page++;
        }
        while (count($orders) === $limit_rows);

        $this->info("Fetched successfully");
    }
}
