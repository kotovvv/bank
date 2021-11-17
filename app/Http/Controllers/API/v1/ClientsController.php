<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Log;
use App\Models\Bank;
use App\Models\Funnel;
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

    public function delBankFromClients(Request $request)
    {
        $data = $request->All();
        return Client::delBankFromClients($data['clients'], $data['bank_id']);
    }

    public function getClientsWithBankAndClient_id(Request $request)
    {
        $data = $request->All();
        //SELECT * FROM `clients` WHERE `user_id` = 109 AND `banksfunnels` LIKE "%\"1:%"  ORDER BY `date_added` DESC
        return Client::where('user_id', $data['user_id'])->where('banksfunnels', 'like', '%"' . $data['bank_id'] . ':%')->orderByDesc('date_added')->get();
    }


    public function getClientsWithoutBanks()
    {
        return Client::where('banksfunnels', '')->orderByDesc('date_added')->limit(10000)->get();
    }


    public function setBankForClients(Request $request)
    {
        $data = $request->All();
        // DebugBar::info( $data);
        if (!isset($data['funnel'])) $data['funnel'] = 0;
        if (isset($data['funnel']) && isset($data['user_id'])) {
            $a_log = [];
            foreach ($data['clients'] as $cl) {
                $a_log = ['client_id' => $cl, 'user_id' => $data['user_id'], 'bank_id' => $data['bank_id'],  'funnel_id' => $data['funnel'], 'dateadd' => Now(), 'timeadd' => Now()];
            }
            Log::insert($a_log);
        }
        $bankfunnels = Client::setBankFunnels($data['clients'], $data['bank_id'], $data['funnel']);
        // Debugbar::info($bankfunnels);
        return response($bankfunnels);
    }

    public function getUserClients($id, $bank_id = null, $funnel = null)
    {
        if ($bank_id == 0 && $funnel == 0) {
            //  SELECT `client_id` FROM `logs` WHERE `user_id` = 1 AND DATE(`date_add`) = DATE(NOW())
            $done_today = Log::select('client_id')->where('user_id', $id)->where('dateadd', date('Y-m-d'))->whereNotNull('bank_id')->get();
            return Client::where('user_id', $id)->whereNotIn('id', $done_today)->orderByDesc('date_added')->get();
        };
        if ($bank_id == 0 && $funnel != null) return Client::where('user_id', $id)->where('banksfunnels', 'like', '%:' . $funnel . '"%')->orderByDesc('date_added')->get();
        if ($bank_id != 0 && $funnel != null) return Client::where('user_id', $id)->where('banksfunnels', 'like', '%:' . $funnel . '"%')->where('banksfunnels', 'like', '%"' . $bank_id . ':%')->orderByDesc('date_added')->get();
        if ($bank_id == null && $funnel == null) return Client::where('user_id', $id)->orderByDesc('date_added')->get();
    }

    public function changeUserOfClients(Request $request)
    {
        $data = $request->All();
        // $a_log = [];
        // foreach ($data['clients'] as $cl) {
        //     $a_log[] = ['client_id' => $cl, 'user_id' => $data['user_id'], 'other' => '0', 'dateadd' => Now(), 'timeadd' => Now()];
        // }
        // Log::insert($a_log);
        return Client::whereIn('id', $data['clients'])->update(['user_id' => $data['user_id'],'date_set'=>date('Y-m-d')]);
    }

    public function getClients(Request $request)
    {
        $a_filter = $request->all();
        if (count($a_filter) == 0) return response([]);
        //Debugbar::info($a_filter);
        $head_sql = "SELECT * FROM `clients` WHERE 1=1";
        $sql = '';
        foreach ($a_filter as $key => $filter_word) {
            // Debugbar::info(iconv_strlen($filter_word));
            switch ($key) {
                case 'datereg':
                    if (strpos($filter_word, ',')) {
                        $a_date = explode(',', $filter_word);
                        $sql .= " AND `registration` BETWEEN date('$a_date[0]') AND date('$a_date[1]')";
                    } else {
                        $sql .= " AND `registration` = date('$filter_word')";
                    }
                    break;
                case 'dateadd':

                    if (strpos($filter_word, ',')) {
                        $a_date = explode(',', $filter_word);
                        $sql .= " AND date(`date_added`) BETWEEN date('$a_date[0]') AND date('$a_date[1]')";
                    } else {
                        $sql .= " AND date(`date_added`) = date('$filter_word')";
                    }
                    break;
                case 'firm':
                    if (iconv_strlen($filter_word) < 4) {
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
                case 'bank_id':
                    $sql .= " AND `banksfunnels` LIKE '%\"" . $filter_word . ":%'";
                    break;
                case 'funnel_id':
                    $sql .= " AND `banksfunnels` LIKE '%:" . $filter_word . "\"%'";
                    break;
                case 'banksfunnelsNotEmpty':
                    $sql .= " AND `banksfunnels` != ''";
                    break;
                case 'user_id':
                    $sql .= " AND `user_id` = '" . $filter_word . "'";
                    break;
                default:
                    $sql .= " AND 1 != 1";
            }
        }
        $sql = $head_sql . $sql . " ORDER BY `date_added` DESC LIMIT 10000";
        // Debugbar::info($sql);
        return DB::select(DB::raw($sql));
    }

public function getReportAll(Request $request){
    $data = $request->All();
    $bank_id = $data['bank_id'];
    $period = $data['period'];
    $a_banks = Bank::get();
    $a_funnels = Funnel::orderBy('order','asc')->get();
    $json = [];
    $json['period'] = $period;
    // SELECT funnel_id,COUNT(*) FROM `logs` WHERE bank_id = 3 AND dateadd = '2021-11-17' GROUP BY funnel_id

    return response($json);
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
