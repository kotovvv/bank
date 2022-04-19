<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\Log;
use Barryvdh\Debugbar\Twig\Extension\Debug;
use Illuminate\Support\Facades\Http;
use DB;
use Debugbar;
use DebugBar\DebugBar as DebugBarDebugBar;
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

        // =======dddddddddddddddddddDDDDDDDDDDDDDDDDDDDDDDDDDDDDDD===========
        return response([

            "status" => "allowed",
            "status_translate" => "Звонок разрешен",
            'fromcash' => 'delete'
        ]);
        // =======dddddddddddddddddddDDDDDDDDDDDDDDDDDDDDDDDDDDDDDD===========


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
                    $regions = json_decode(Storage::get('regions' . $bank_id . '.json'));
                    Cache::store('file')->put('regions' . $bank_id, $regions, 2592000);
                } else {
                    Artisan::call("Alfa:references");
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

        // $a_ans = $response->json();
        // $entries = $response->object()->entries;
        $entries = $response['entries'];

        // $hm_page = ceil($a_ans['total_entries'] / $a_ans['per_page']);
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
