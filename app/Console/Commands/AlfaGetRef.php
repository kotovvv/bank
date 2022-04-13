<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Bank;
use Illuminate\Support\Facades\Http;

class AlfaGetRef extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Alfa:references';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get references for region\city for bank Alfa';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //2=Alfa bank
        $bank_id = 2;
        $bank = Bank::where('id', $bank_id)->first();
        //get regions
        $action = 'dictionaries?code=regions';
        $response = Http::withHeaders([
            'API-key' => $bank['token'],
            'Content-Type' => 'application/json; charset=UTF-8'
        ])->get($bank['url'] . $action);
        if ($response->successful()) {
            $regions = json_decode($response)->values;
            $entries = [];
            foreach ($regions as $region) {
                $entries[] = ['id' => $region->fias, 'name' => $region->name];
            }
            $regions = $entries;
            Storage::disk('local')->put('regions' . $bank_id . '.json', json_encode($regions, JSON_UNESCAPED_UNICODE));
        } else {
            Storage::disk('local')->put('regions' . $bank_id . '.error', 'error' . $response);
        }
        //get cities
        $action = 'dictionaries?code=cities';
        $response = Http::withHeaders([
            'API-key' => $bank['token'],
            'Content-Type' => 'application/json; charset=UTF-8'
        ])->get($bank['url'] . $action);
        if ($response->successful()) {
            $sities = json_decode($response)->values;
            Storage::disk('local')->put('cities' . $bank_id . '.json', json_encode($sities, JSON_UNESCAPED_UNICODE));
        } else {
            Storage::disk('local')->put('cities' . $bank_id . '.error', 'error' . $response);
        }

        return Command::SUCCESS;
    }
}
