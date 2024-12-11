<?php

namespace App\Console\Commands;

use App\Models\Income;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchIncomes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:incomes {dateFrom} {dateTo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch incomes from API';

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

        $apiUrl = "http://$apiIp:$apiPort/api/incomes";
        $page = 1;
        $limit_rows = 500;

        $this->info("Start fetching incomes from API with dates from $dateFrom to $dateTo");

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
                $this->error('Error fetching incomes from API :(');
                return;
            }

            $incomes = $data['data'] ?? [];

            foreach ($incomes as $income) {
                Income::create([
                    'income_id' => $income['income_id'],
                    'number' => $income['number'],
                    'date' => $income['date'],
                    'last_change_date' => $income['last_change_date'],
                    'supplier_article' => $income['supplier_article'],
                    'tech_size' => $income['tech_size'],
                    'barcode' => $income['barcode'],
                    'quantity' => $income['quantity'],
                    'total_price' => $income['total_price'],
                    'date_close' => $income['date_close'],
                    'warehouse_name' => $income['warehouse_name'],
                    'nm_id' => $income['nm_id'],
                ]);
            }
            $this->info("Page $page of $totalPages incomes fetched");

            $page++;
        }
        while (count($incomes) === $limit_rows);

        $this->info("Fetched successfully");
    }
}
