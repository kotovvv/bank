<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use DB;
use Debugbar;



class ClientsController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    // return User::All();

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
    //
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

  public function one()
  {
    $clients = Client::all();
    foreach($clients as $client){
    for($i=1; $i < 100; $i++){
        $time = rand(1577861163,1633070763);
        $date = date('Y-m-d', $time);
        $client['organizationName'] = str_replace(['ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ ','ИП ','АКЦИОНЕРНОЕ ОБЩЕСТВО '],'',$client['organizationName']);
        $org = ['ИП','Общество с ограниченной ответсвенностью', "АКЦИОНЕРНОЕ ОБЩЕСТВО"];
      $item = [
        'inn' => rand('11111111111','99999999999'),
        'fullName' => $client['fullName'],
        'phoneNumber' => $client['phoneNumber'],
        'organizationName' => $org[array_rand($org)].' '.$client['organizationName'],
        'address' => $client['address'],
        'region' => $client['region'],
        'registration' => $date,
        'initiator' => $client['']
      ];
      Client::create($item);
    }
    }
    return response('All done. Ser!');
  }


  public function getClients(Request $request)
  {
    return Client::limit(10000)->get();
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
