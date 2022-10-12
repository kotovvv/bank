<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use Excel;
use DB;
use Debugbar;
use App\Exports\ClientExport;

class ImportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return Import::select(['imports.*', DB::raw('(SELECT `name` FROM `providers` WHERE `id` = `provider_id`) AS provider'), DB::raw('(SELECT `name` FROM `users` WHERE `id` = `user_id`) AS user')])->orderByDesc('end')->get();
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
    public function export()
    {
        //return Excel::download(new ClientExport, 'users.xlsx');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $duplicate = $all = $inserted = $error = 0;

        $a_imp = $request->all();
        $all = count($a_imp['clients']);

        foreach ($a_imp['clients'] as $client_val) {
            // $a_client = array_combine($a_imp['headers'], $client_val);
            if(count($client_val) < 5) {$error++;continue;}
            if(strlen($client_val['inn']) > 12 || strlen($client_val['inn']) < 9 ) {$error++;continue;}
            if(strlen($client_val['inn']) == 11 || strlen($client_val['inn']) == 9 ){
                $client_val['inn'] = '0'.$client_val['inn'];
            }
            if($client_val['fullName'] == '' && strstr($client_val['organizationName'],'ИП')){
                $client_val['fullName'] = trim(str_replace('ИП', '',$client_val['organizationName']));
            }
            if(strlen($client_val['inn']) == 12){
                 $client_val['organizationName'] = "ИП ".$client_val['fullName'];
            }
            if (Client::where('inn', $client_val['inn'])->exists()) {
                $duplicate++;
                continue;
            }
            // $client_val['phoneNumber'] = str_replace(['+', '(', ')', '#', ' ', '-', '_'], '', $client_val['phoneNumber']);

            $client_val['phoneNumber'] = str_replace('/\D/', '', $client_val['phoneNumber']);
            if (strlen($client_val['phoneNumber']) == 10 && !in_array(substr($client_val['phoneNumber'], 0, 1), ['7', '8'])) $client_val['phoneNumber'] = '7'.$client_val['phoneNumber'];
            if (strlen($client_val['phoneNumber']) != 11) {$error++;continue;}
            $client_val['phoneNumber'] =  preg_replace('/^8/', '7',$client_val['phoneNumber'], 1 );
            $client_val = array_merge($client_val, ['date_added' => date("Y-m-d H:i:s")]);
            $client_val['registration'] = date( 'Y-m-d', strtotime( $client_val['registration'] ) );

            try {
                DB::table('clients')->insert($client_val);
              } catch(\Illuminate\Database\QueryException $ex){
                dd($ex->getMessage());
                $error++;
              }
            $inserted++;
        }
        return response()->json([
            'status' => 200,
            'duplicate' => $duplicate,
            'inserted'   => $inserted,
            'error' => $error,
            'all' => $all
        ]);
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
    public function update(Request $request, $id)
    {
        //
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
