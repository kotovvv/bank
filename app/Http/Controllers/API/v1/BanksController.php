<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bank;
use Barryvdh\Debugbar\Twig\Extension\Debug;
use Illuminate\Support\Facades\Http;
use DB;
use Debugbar;
use Illuminate\Support\Facades\Cache;


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

        $a_bank_actions = [
            'call_requests' => '/api/v1/call_easy/call_requests',
        ];

        $data = $request->All();
        $inn = $data['data']['inn'];
       if( Cache::has($inn)){
        //    Debugbar::info('inn '.$inn);
           $response = [
            "id" => Cache::get($inn),
            "status"=>"allowed",
            "status_translate"=>"Звонок разрешен"
           ];
        return response($response);
       }

        $send = ['data' => $data['data']];

        if (isset($data['bank_id'])) {
            $bank = Bank::where('id', $data['bank_id'])->first();
            $action = $a_bank_actions[$data['action']];

            $response = Http::withHeaders([
                'Authorization' => 'Token token=' . $bank['token'],
                'Content-Type' => 'application/json'
            ])->post(
                $bank['url'] . $action,
                $send
            );
        }
        $res = json_decode($response);
        if(isset($res['id'])){
            Debugbar::info('id '.$res['inn'], $res['id']);
            Cache::put($res['inn'], $res['id'], 60*60);
        }
        return response($response);
    }

    public function updateStatus(Request $request)
    {
        $data = $request->All();
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

        return response($response);
    }


    public function getRegions( $bank_id)
    {
        if (Cache::store('file')->has('regions')) {
            $entries = Cache::store('file')->get('regions');
        } else {

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
            Cache::store('file')->put('regions', $entries, 2592000);
        }
        return response($entries);
    }


    public function getCities(Request $request)
    {
        $data = $request->All();
        $bank_id = $data['bank_id'];
        $region_id = $data['region_id'];
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
        $a_bank_actions = [
            'send_order' => '/api/v1/sber_mq/order',
        ];

        $data = $request->All();
        $send = ['data' => $data['data']];
        if (isset($data['bank_id'])) {
            $bank = Bank::where('id', $data['bank_id'])->first();
            $action = $a_bank_actions[$data['action']];

            $response = Http::withHeaders([
                'Authorization' => 'Token token=' . $bank['token'],
                'Content-Type' => 'application/json'
            ])->post(
                $bank['url'] . $action,
                $send
            );
        }

        return response(['data' => $response->object(), 'successful' => $response->successful(), 'status' => $response->status()]);
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
