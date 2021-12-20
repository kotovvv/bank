<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bank;
use Illuminate\Support\Facades\Http;
use DB;
use Debugbar;


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
        $send = ['data'=>$data['data']];
        if (isset($data['bank_id'])) {
            $bank = Bank::where('id', $data['bank_id'])->first();
            $action = $a_bank_actions[$data['action']];

            $response = Http::withHeaders([
                'Authorization' => 'Token token=' . $bank['token'],
                'Content-Type' => 'application/json'
            ])->post($bank['url'] . $action,
                $send
            );
        }

        return response($response);
    }

    public function updateStatus(Request $request)
    {
        $data = $request->All();
        $a_bank_actions = [
            'updateStatus' => '/api/v1/call_easy/call_requests/'.$data['client_id'],
        ];

        $send = ['data'=>$data['data']];
        if (isset($data['bank_id'])) {
            $bank = Bank::where('id', $data['bank_id'])->first();
            $action = $a_bank_actions[$data['action']];

            $response = Http::withHeaders([
                'Authorization' => 'Token token=' . $bank['token'],
                'Content-Type' => 'application/json'
            ])->patch($bank['url'] . $action,
                $send
            );
        }

        return response($response);
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
