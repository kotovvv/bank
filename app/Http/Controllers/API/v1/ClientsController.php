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
				foreach ($clients as $client) {
						for ($i = 1; $i < 100; $i++) {
								$time = rand(1577861163, 1633070763);
								$date = date('Y-m-d', $time);
								$client['organizationName'] = str_replace(['ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ ', 'ИП ', 'АКЦИОНЕРНОЕ ОБЩЕСТВО '], '', $client['organizationName']);
								$org = ['ИП', 'Общество с ограниченной ответсвенностью', "АКЦИОНЕРНОЕ ОБЩЕСТВО"];
								$item = [
										'inn' => rand('11111111111', '99999999999'),
										'fullName' => $client['fullName'],
										'phoneNumber' => $client['phoneNumber'],
										'organizationName' => $org[array_rand($org)] . ' ' . $client['organizationName'],
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
				$a_filter = $request->all();
				//Debugbar::info($a_filter);
				$sql = "SELECT * FROM `clients` WHERE 1=1";
				foreach ($a_filter as $key => $filter_word) {

						switch ($key) {
								case 'datereg':
										if (strpos($filter_word, ',')) {
												$a_date = explode(',', $filter_word);
												$sql .= " AND `registration` BETWEEN date($a_date[0]) AND date($a_date[1])";
										} else {
												$sql .= " AND `registration` = date($filter_word)";
										}
										break;
								case 'dateadd':

										if (strpos($filter_word, ',')) {
												$a_date = explode(',', $filter_word);
												$sql .= " AND date(`date_added`) BETWEEN date($a_date[0]) AND date($a_date[1])";
										} else {
												$sql .= " AND date(`date_added`) = date($filter_word)";
										}
										break;
								case 'firm':
										if (strlen($filter_word) < 4) {
												$sql .= " AND `organizationName` LIKE ('" . $filter_word . "%')";
										} else {
												$sql .= " AND MATCH (organizationName) AGAINST ('" . $filter_word . "')";
										}
										break;
								case 'fio':
										$sql .= " AND MATCH (fullName) AGAINST ('" . $filter_word . "')";
										break;
								case 'inn':
										$sql .= " AND `inn` = '" . $filter_word . "'";
										break;
								case 'address':
										$sql .= " AND MATCH (address) AGAINST ('" . $filter_word . "')";
										break;
								case 'region':
										$sql .= " AND MATCH (region) AGAINST ('" . $filter_word . "')";
										break;
						}
				}

				return DB::select(DB::raw($sql));
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
