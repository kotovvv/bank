<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Log;
use App\Models\Bank;
use App\Models\Funnel;
use App\Models\User;
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
        if ($bank_id === 0 && $funnel === 0) return Client::where('user_id', $id)->where('banksfunnels', 'like', '%:0"%')->orderByDesc('date_added')->get();
        if ($bank_id == 0 && $funnel != null) return Client::where('user_id', $id)->where('banksfunnels', 'like', '%:' . $funnel . '"%')->orderByDesc('date_added')->get();
        if ($bank_id != 0 && $funnel != null) return Client::where('user_id', $id)->where('banksfunnels', 'like', '%:' . $funnel . '"%')->where('banksfunnels', 'like', '%"' . $bank_id . ':%')->orderByDesc('date_added')->get();
        if ($bank_id === null && $funnel === null) return Client::where('user_id', $id)->orderByDesc('date_added')->get();
    }

    public function changeUserOfClients(Request $request)
    {
        $data = $request->All();
        // $a_log = [];
        // foreach ($data['clients'] as $cl) {
        //     $a_log[] = ['client_id' => $cl, 'user_id' => $data['user_id'], 'other' => '0', 'dateadd' => Now(), 'timeadd' => Now()];
        // }
        // Log::insert($a_log);
        return Client::whereIn('id', $data['clients'])->update(['user_id' => $data['user_id'], 'date_set' => date('Y-m-d')]);
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
                        if ($a_date[0] == $a_date[1]) {
                            $sql .= " AND `registration` = date('$filter_word')";
                        } else {
                            $sql .= " AND `registration` BETWEEN date('$a_date[0]') AND date('$a_date[1]')";
                        }
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

    public function getReportAll(Request $request)
    {
        $data = $request->All();
        $bank_id = $data['bank_id'];
        $period = $data['period'];
        $done_count = 0;
        $where_period = '';
        if (is_array($period)) {
            if (count($period) == 1) {
                $where_period .= " = date('$period[0]')";
            } else {
                if ($period[0] == $period[1]) {
                    $where_period .= " = date('$period[0]')";
                } else {
                    $where_period .= " BETWEEN date('$period[0]') AND date('$period[1]')";
                }
            }
        } else {
            $where_period .= " = date('$period')";
        }

        if ($bank_id == 0) {
            $a_banks = Bank::get();
            $sql = "SELECT COUNT(*) COUNT FROM `clients` WHERE `date_set` " . $where_period;
            $all_count = DB::select(DB::raw($sql));
            $sql = "SELECT l.`user_id`,l.`funnel_id`,l.`bank_id` FROM `logs` l LEFT JOIN `funnels` f ON (f.`id` = l.`funnel_id`) WHERE `dateadd` " . $where_period . "ORDER BY f.order ASC";
            $usr_bnk_fnl = DB::select(DB::raw($sql));
        } else {
            $a_banks =  Bank::where('id', $bank_id)->get();
            $sql = "SELECT COUNT(*) COUNT FROM `clients` WHERE `banksfunnels` LIKE '%\"" . $bank_id . ":%' AND `date_set` " . $where_period;
            $all_count = DB::select(DB::raw($sql));
            $sql = "SELECT l.`user_id`,l.`funnel_id`,l.`bank_id` FROM `logs` l LEFT JOIN `funnels` f ON (f.`id` = l.`funnel_id`) WHERE `bank_id` = '" . $bank_id . "' AND `dateadd` " . $where_period . " ORDER BY f.order ASC";
            $usr_bnk_fnl = DB::select(DB::raw($sql));
        }
        $a_funnels = Funnel::orderBy('order', 'asc')->get()->toArray();

        $a_banks_o = array_unique(array_column($usr_bnk_fnl, 'bank_id'));
        $a_funnels_o = array_unique(array_column($usr_bnk_fnl, 'funnel_id'));
        $a_users_o = array_unique(array_column($usr_bnk_fnl, 'user_id'));
        $a_users = User::whereIn('id', $a_users_o)->get();

        $json = [];
        $td = [];
        $sum = [];
        $json['header'] = array_merge([implode("  ", $period)], array_column($a_funnels, 'name', 'id'));

        foreach ($a_banks_o as $bank) {
            foreach ($a_users_o as $user) {
                foreach($a_funnels_o as $funnel ){

                }
            }
        }

        foreach ($a_banks as $bank) {
            $a_row = [];
            $a_row[] = $bank['name'];
            $sql = "SELECT funnel_id,COUNT(*) count FROM `logs` WHERE bank_id = '" . $bank['id'] . "' AND `dateadd`" . $where_period  . " GROUP BY funnel_id";
            $funnels_count = DB::select(DB::raw($sql));
            foreach ($a_funnels as $funnel) {
                $count = '';
                if (!isset($sum[$funnel['id']])) $sum[$funnel['id']] = 0;
                foreach ($funnels_count as $fc) {
                    if ($fc->funnel_id == $funnel['id']) {
                        $count = $fc->count;
                    }
                }
                $sum[$funnel['id']] += $count == '' ? 0 : $count;
                $a_row[] =  $count;
            }
            $td[] = $a_row;
        }
        $sum = array_merge(['Итого'], $sum);
        $json['td'] = $td;
        $all = array_merge(array_fill(0, count($a_funnels), ''), ['Назначено: ' . $all_count[0]->COUNT]);
        $done = array_merge(array_fill(0, count($a_funnels), ''), ['Обработано: ' . $done_count]);
        $json['td'] = array_merge($json['td'], [$sum], [$all], [$done]);

        return response($json);
    }

    //report for users
    //SELECT `user_id`,`bank_id`,`funnel_id`,COUNT(funnel_id) hm FROM `logs`  GROUP BY `funnel_id`,user_id ORDER BY user_id ASC


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
