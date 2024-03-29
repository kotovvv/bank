<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use App\Models\Bank;
use App\Models\Log;
use App\Models\Client;
//use Barryvdh\Debugbar\Twig\Extension\Debug;
use Illuminate\Support\Facades\Http;
//use DB;
//use Debugbar;
use Illuminate\Support\Facades\Log as Loging;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;


class BanksController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Bank::All();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // DebugBar::info();
        $data = $request->All();
        if (array_key_exists('id', $data)) {
            $id = $data['id'];
            unset($data['id']);
            Bank::where('id', $id)->update($data);
        } else {
            Bank::create($data);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
    }


    public function canTel(Request $request)
    {

        if (env('APP_ENV') == 'local') {
            return response([
                "status" => "allowed",
                "status_translate" => "Звонок разрешен",
                'fromcash' => 'delete'
            ]);
        }



        $data = $request->All();
        $inn = $data['data']['inn'];
        if (Cache::has($inn)) {
            $response = [
                "id" => Cache::get($inn),
                "status" => "allowed",
                "status_translate" => "Звонок разрешен",
                'fromcash' => true
            ];
            return response($response);
        }

        if (isset($data['bank_id'])) {
            $bank = Bank::where('id', $data['bank_id'])->first();
            $a_bank_actions = [
                1 => [
                    'action' => '/api/v1/call_easy/call_requests',
                    'header' => [
                        'Authorization' => 'Token token=' . $bank['token'],
                        'Content-Type' => 'application/json'
                    ]
                ],
                2 => [
                    'action' => 'checks',
                    'header' => [
                        'API-key' => $bank['token'],
                        'Content-Type' => 'application/json; charset=UTF-8'
                    ]
                ]
            ];

            $action = $a_bank_actions[$data['bank_id']]['action'];
            if ($data['bank_id'] == 1) {
                $send = ['data' => $data['data']];
                $response = Http::withHeaders($a_bank_actions[$data['bank_id']]['header'])->post(
                    $bank['url'] . $action,
                    $send
                );
            }
            if ($data['bank_id'] == 2) {
                $send = json_encode([
                    "organizationInfo" => ["inn" => $inn],
                    "contactInfo" => [
                        ['phoneNumber' => str_replace('+', '', $data['data']['phone'])]
                    ],
                    "productInfo" => [
                        ["productCode" => "LP_RKO"]
                    ]
                ]);
                $response = Http::withBody($send, 'application/json')->withHeaders($a_bank_actions[$data['bank_id']]['header'])->post(
                    $bank['url'] . $action
                );
            }


            if ($data['bank_id'] == 1) {
                $res = json_decode($response);
                if (isset($res->id) && $res->status == 'allowed') {
                    Cache::put($res->inn, $res->id, 60 * 60);
                }
                return response($response);
            }
            if ($data['bank_id'] == 2) {
                $res = [];
                if ($response->status() == 200) {
                    $res['status']  = "allowed";
                    $res['status_translate']  = "Звонок разрешен";
                    Cache::put($inn, $inn, 60 * 60);
                } else {
                    $res['status']  = "forbidden";
                }
                return response($res);
            }
        }
        return response('error');
    }

    public function updateStatus(Request $request)
    {
        $data = $request->All();
        $response = [];
        if ($data['bank_id'] == 1) {
            $a_bank_actions = [
                'updateStatus' => '/api/v1/call_easy/call_requests/' . $data['client_id'],
            ];

            $send = ['data' => $data['data']];
            if (isset($data['bank_id'])) {
                $bank = Bank::where('id', $data['bank_id'])->first();
                $action = $a_bank_actions[$data['action']];

                $response = Http::withHeaders([
                    'Authorization' => 'Token token=' . $bank['token'],
                    'Content-Type' => 'application/json'
                ])->patch(
                    $bank['url'] . $action,
                    $send
                );
            }
        }
        return response($response);
    }


    public function getRegions($bank_id)
    {

        if (Cache::store('file')->has('regions' . $bank_id)) {
            $entries = Cache::store('file')->get('regions' . $bank_id);
        } else {
            if ($bank_id == 1) {
                $a_bank_actions = [
                    'region' => '/api/v1/sber_mq/region?with_merchant_branches=1',
                ];

                $bank = Bank::where('id', $bank_id)->first();
                $action = $a_bank_actions['region'];

                $response = Http::withHeaders([
                    'Authorization' => 'Token token=' . $bank['token'],
                    'Content-Type' => 'application/json'
                ])->get($bank['url'] . $action);

                $entries = $response['entries'];

                $hm_page = ceil($response['total_entries'] / $response['per_page']);
                if ($hm_page > 1) {
                    for ($i = 2; $i <= $hm_page; $i++) {
                        $resp = Http::withHeaders([
                            'Authorization' => 'Token token=' . $bank['token'],
                            'Content-Type' => 'application/json'
                        ])->get($bank['url'] . $action . '&page=' . $i);
                        $entries = array_merge($entries, $resp->object()->entries);
                    }
                }
                Cache::store('file')->put('regions' . $bank_id, $entries, 2592000);

            }
            if ($bank_id == 2) {
                if (Storage::exists('regions' . $bank_id . '.json')) {
                    $entries = json_decode(Storage::get('regions' . $bank_id . '.json'));
                    Cache::store('file')->put('regions' . $bank_id, $entries, 2592000);

                } else {
                    Artisan::call("Alfa:references");
                    $entries = [];
                }
            }
        }
        return response($entries);
    }


    public function getCities(Request $request)
    {
        $data = $request->All();
        $bank_id = $data['bank_id'];
        $region_id = $data['region_id'];
        if ($bank_id == 1) {
            $query = '';
            if (isset($data['query'])) $query = '&query=' . urlencode($data['query']);
            // api/v1/sber_mq/city?with_merchant_branches=1&region_id={region_id}
            $a_bank_actions = [
                'getCities' => '/api/v1/sber_mq/city?region_id=' . $region_id . $query,
            ];

            $bank = Bank::where('id', $bank_id)->first();
            $action = $a_bank_actions['getCities'];

            $response = Http::withHeaders([
                'Authorization' => 'Token token=' . $bank['token'],
                'Content-Type' => 'application/json'
            ])->get($bank['url'] . $action);
        }
        if ($bank_id == 2) {
            if (Storage::exists('cities' . $bank_id . '.json')) {
                $cities = json_decode(Storage::get('cities' . $bank_id . '.json'));
                $entries = [];
                foreach ($cities as $city) {
                    if ($city->regionFias == $region_id) {
                        $entries[] = ['id' => $city->fias, 'name' => $city->name];
                    }
                }
                $response['entries'] = $entries;
            }
        }
        return response($response);
    }

    public function getBranches(Request $request)
    {
        $data = $request->All();
        $bank_id = $data['bank_id'];
        $region_id = $data['region_id'];
        $city_id = $data['city_id'];

        $a_bank_actions = [
            'getbranches' => '/api/v1/sber_mq/merchant_branch?with_merchant_branches=1&region_id=' . $region_id . '&city_id=' . $city_id,
        ];

        $bank = Bank::where('id', $bank_id)->first();
        $action = $a_bank_actions['getbranches'];

        $response = Http::withHeaders([
            'Authorization' => 'Token token=' . $bank['token'],
            'Content-Type' => 'application/json'
        ])->get($bank['url'] . $action);

        $entries = $response['entries'];

        $hm_page = ceil($response['total_entries'] / $response['per_page']);
        if ($hm_page > 1) {
            for ($i = 2; $i <= $hm_page; $i++) {
                $resp = Http::withHeaders([
                    'Authorization' => 'Token token=' . $bank['token'],
                    'Content-Type' => 'application/json'
                ])->get($bank['url'] . $action . '&page=' . $i);
                $entries = array_merge($entries, $resp->object()->entries);
            }
        }

        return response($entries);
    }

    public function sendOrder(Request $request)
    {
        // POST api/v1/sber_mq/order
        $data = $request->All();
        $bank = Bank::where('id', $data['bank_id'])->first();
        $response = (object)[];
        // Sber bank
        if ($data['bank_id'] == 1) {
            $send = ['data' => $data['data']];
            $action = '/api/v1/sber_mq/order';
            $response = Http::withHeaders([
                'Authorization' => 'Token token=' . $bank['token'],
                'Content-Type' => 'application/json'
            ])->post(
                $bank['url'] . $action,
                $send
            );
            if (isset($response->id)) {
                $data['add_info'] += $response->id;
            }
            if ($data['add_info']) {
                $this->addInfo($data);
            }
            return response(['data' => $response->object(), 'successful' => $response->successful(), 'status' => $response->status()]);
        }

        // Alfa bank
        if ($data['bank_id'] == 2) {
            $action = 'leads';
            $header = [
                'API-key' => $bank['token'],
                'Content-Type' => 'application/json; charset=UTF-8'
            ];
            $send = json_encode($data['data'], JSON_UNESCAPED_UNICODE);

            $response = Http::withBody($send, 'application/json')->withHeaders($header)->post(
                $bank['url'] . $action
            );
            if (isset($response->id)) {
                $data['add_info'] += $response->id;
            }
            if ($data['add_info']) {
                $this->addInfo($data);
            }

            return response(['data' => $response->object(), 'successful' => $response->successful(), 'status' => $response->status()]);
        }

        return;
    }

    private function addInfo($data)
    {
        $a_log = [];
        $a_log = ['client_id' => $data['client_id'], 'user_id' => $data['user_id'], 'bank_id' => $data['bank_id'],  'other' => $data['add_info'], 'dateadd' => Now(), 'timeadd' => Now()];
        Log::insert($a_log);
    }

    private function getVTBToken($bank)
    {
        $id_cod = explode(':', $bank['token']);
        if (count($id_cod) != 2) return response(['successful' => false, 'message' => 'Установите в банке ID:Cod']);
        $data = ["grant_type" => "client_credentials", "client_id" => $id_cod[0], "client_secret" => $id_cod[1]];
        $response = Http::asForm()->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->post('https://passport.api.vtb.ru/passport/oauth2/token', $data);

        if (isset($response['access_token'])) return ['successful' => true, 'token' => $response['access_token']];

        return ['data' => $response->object(), 'successful' => false, 'status' => $response->error_description];
    }

    public function chekLidsVTB(Request $request)
    {
        $data = $request->All();
        $bank = Bank::where('id', $data['bank_id'])->first();
        $clients = Client::whereIn('id', $data['clients'])->get();

        $sendleads = [];
        if ($clients) {
            foreach ($clients as $client) {
                $sendleads[] = (object)['inn' => $client['inn'], 'productCode' => 'Payments'];
            }
        }

        $allow = $desallow = 0;
        $rclientsAll = $rclients = [];
        for ($step = 0, $show = 50; (count($sendleads) - $step * $show) >= 0; $step++) {
            $stepleads = ['leads' => array_slice($sendleads, $step * $show, $show )];
            $sendClients = $clients->slice($step * $show, $show);
            // get token from bank
            $status_token = $this->getVTBToken($bank);
            // $time_start = microtime(true);
            if (isset($status_token['token']) && $status_token['successful'] == true) {
                try {
                    // send lids in bank check duble
                    $response = Http::retry(5, 10000)->withHeaders([
                        'Authorization' => 'Bearer ' . $status_token['token'],
                        'Content-Type' => 'application/json'
                    ])->post($bank['url'] . 'check_leads', $stepleads);

                } catch (RequestException $e) {
                    //  Loging::info($response->headers());
                     Loging::info( $e);
                    // return response(['message' => $e, 'successful' => false]);
                }
                // Loging::info('TIME after chek leads: '.time());
                if ($response->status() == 500) {
                    Loging::info('Response 500: ');
                    Loging::info($response);
                    // return response(['message' => 'Сервер не отвечает', 'successful' => false]);
                }
                if ($response->status() == 200) {
                    // get array from ansver bank
                    $rclients = json_decode($response->body('data'))->leads;
                    $a_inns_status = [];
                    foreach ($rclients as $lead) {
                        $a_inns_status[$lead->responseCode][] = $lead->inn;
                    }

                    // merge clients and response
                    foreach ($sendClients as $o_client) {
                        foreach ($rclients as $o_rclient) {
                            if ($o_rclient->inn == $o_client->inn) {
                                if ($o_rclient->responseCode == 'POSITIVE') {
                                    Client::setBankFunnels([$o_client->id], $bank['id'], 0);
                                    Log::insert(['client_id' => $o_client->id, 'user_id' => $data['user_id'], 'bank_id' => $bank['id'],  'other' => 'response bank ' . $o_rclient->responseCode, 'funnel_id' => 0, 'dateadd' => Now(), 'timeadd' => Now()]);
                                    $allow++;
                                } else {
                                    Client::setBankFunnels([$o_client->id], $bank['id'], 2);
                                    Log::insert(['client_id' => $o_client->id, 'user_id' => $data['user_id'], 'bank_id' => $bank['id'],  'other' => 'response bank ' . $o_rclient->responseCode, 'funnel_id' => 2, 'dateadd' => Now(), 'timeadd' => Now()]);
                                    $desallow++;
                                }
                            }
                        }
                    }
                    $rclientsAll = array_merge($rclientsAll, $rclients);
                }
                // $time_end = microtime(true);
                // $execution_time = ($time_end - $time_start)/60;
                // Loging::info('TIME execution_time: '.$execution_time);
            } else {
                return response(['message' => $status_token, 'successful' => false]);
            }
            // sleep(15);
        }
        //  Loging::info('END END END: allow='.$allow." desallow=".$desallow);
         return response(['rclients' => $rclientsAll, 'allow' => $allow, 'desallow' => $desallow, 'successful' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
