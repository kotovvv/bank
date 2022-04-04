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
use App\Http\Controllers\API\v1\BanksController;
use Illuminate\Support\Facades\Log as Logg;


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

    public function delClients(Request $request)
    {
        $data = $request->All();
        Client::destroy($data['client_ids']);
        return count($data['client_ids']);
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
        return Client::where('banksfunnels', '')->orderByDesc('date_added')->get(); //->limit(100000)
    }


    public function setBankForClients(Request $request)
    {
        ini_set('max_execution_time', '360');
        $data = $request->All();
        // DebugBar::info( $data);
        if (!isset($data['funnel'])) $data['funnel'] = 0;
        if (!isset($data['other'])) $data['other'] = '';
        if (isset($data['funnel']) && isset($data['user_id'])) {
            $a_log = [];
            foreach ($data['clients'] as $cl) {
                $a_log = ['client_id' => $cl, 'user_id' => $data['user_id'], 'bank_id' => $data['bank_id'], 'funnel_id' => $data['funnel'], 'other' => $data['other'], 'dateadd' => Now(), 'timeadd' => Now()];
            }
            Log::insert($a_log);
        }
        // Check clinets in bank
        if (isset($data['checkBanks']) && $data['checkBanks']) {
            // ini_set('memory_limit', '-1');
            // set_time_limit(0);
            $i = 0;
            $clients = Client::select('id', 'inn', 'phoneNumber')->whereIn('id', $data['clients'])->get();
            $all = count($clients);
            $cl_onstat = [];
            $Banks = new BanksController();
            foreach ($clients as $cl) {
                $request = new Request([
                    'bank_id' => $data['bank_id'],
                    'data' => ['phone' => "+" . $cl->phoneNumber, 'inn' => $cl->inn],
                    'action' => "call_requests",
                ]);
                $res = $Banks->canTel($request);
                $res = json_decode($res->content(), true);
                if (isset($res['errors'])) {
                    Logg::info([
                        'bank_id' => $data['bank_id'],
                        'phone' => "+" . $cl->phoneNumber,
                        'inn' => $cl->inn
                    ]);
                    Logg::info($res['errors']);
                    continue;
                }

                if (isset($res['status']) && ($res['status'] == 'forbidden' || $res['status'] == 'blocked')) {
                    $cl_onstat['2'][] = $cl['id'];
                    // Client::setBankFunnels([$cl['id']], $data['bank_id'], 2);
                } else {
                    $cl_onstat['0'][] = $cl['id'];
                    // Client::setBankFunnels([$cl['id']], $data['bank_id'], 0);
                    $i++;
                }
                usleep(10);
            }
            // Debugbar::info($cl_onstat);
            if (isset($cl_onstat['2']) && count($cl_onstat['2'])) {
                Client::setBankFunnels($cl_onstat['2'], $data['bank_id'], 2);
            }
            if (isset($cl_onstat['0']) && count($cl_onstat['0'])) {
                Client::setBankFunnels($cl_onstat['0'], $data['bank_id'], 0);
            }

            return ['all' => $all, 'done' => $i];
        } else {
            $bankfunnels = Client::setBankFunnels($data['clients'], $data['bank_id'], $data['funnel']);
        }
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
        return Client::whereIn('id', $data['clients'])->update(['user_id' => $data['user_id'], 'date_set' => date('Y-m-d'), 'time_set' => date('H:i:s')]);
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
                case 'banks':
                    $str = ' AND (';
                    foreach ($filter_word as $key => $bank_id) {
                        $or = $key > 0 ? " OR " : "";
                        $str .= $or . " `banksfunnels` LIKE '%\"" . $bank_id . ":%'";
                    }
                    $str .= ') ';
                    $sql .= $str;
                    break;
                case 'funnels':
                    $str = ' AND (';
                    foreach ($filter_word as $key => $funnel) {
                        $or = $key > 0 ? " OR " : "";
                        $str .= $or . " `banksfunnels` LIKE '%:" . $funnel . "\"%'";
                    }
                    $str .= ') ';
                    $sql .= $str;
                    break;
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
                    if (is_array($filter_word)) {
                        $a_date = $filter_word;
                    } else {
                        if (strpos($filter_word, ',')) {
                            $a_date = explode(',', $filter_word);
                        } else {
                            $a_date[] = $filter_word;
                        }
                    }
                    if (count($a_date) == 2) {
                        $sql .= " AND date(`date_added`) BETWEEN date('$a_date[0]') AND date('$a_date[1]')";
                    } else {
                        $sql .= " AND date(`date_added`) = date('$a_date[0]')";
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
                case 'bankfunnels':
                    $sql .= " AND `banksfunnels` LIKE '%\"" . $filter_word . "\"%'";
                    break;
                case 'bank_id':
                    $sql .= " AND `banksfunnels` LIKE '%\"" . $filter_word . ":%'";
                    break;
                case 'funnel_id':
                    $sql .= " AND `banksfunnels` LIKE '%:" . $filter_word . "\"%'";
                    break;
                case 'banksfunnelsNotEmpty':
                    $sql .= " AND `banksfunnels` LIKE '%:0\"%'";
                    break;
                case 'user_id':
                    $sql .= " AND `user_id` = '" . $filter_word . "'";
                    break;
                default:
                    $sql .= " AND 1 != 1";
            }
        }
        $sql = $head_sql . $sql . " ORDER BY `date_added` DESC "; //LIMIT 100000
        // Debugbar::info($sql);
        return DB::select(DB::raw($sql));
    }

    public function getReportAll(Request $request)
    {
        $data = $request->All();
        $bank_id = $data['bank_id'];
        $period = $data['period'];
        $where_user = isset($data['user_id']) ? ' AND l.`user_id` = ' . (int) $data['user_id'] : '';
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
            $sql = "SELECT COUNT(*) COUNT FROM `clients` WHERE `date_set` " . $where_period;
            $all_count = DB::select(DB::raw($sql));
            $sql = "SELECT `user_id`,`funnel_id`,`bank_id` FROM `logs` l WHERE `dateadd` " . $where_period . $where_user;
            $usr_bnk_fnl = DB::select(DB::raw($sql));
            $a_banks_o = array_unique(array_column($usr_bnk_fnl, 'bank_id'));
            $a_banks = Bank::whereIn('id', $a_banks_o)->get();
            $where_bank = '';
        } else {
            $a_banks =  Bank::where('id', $bank_id)->get();
            $sql = "SELECT COUNT(*) COUNT FROM `clients` WHERE `banksfunnels` LIKE '%\"" . $bank_id . ":%' AND `date_set` " . $where_period;
            $all_count = DB::select(DB::raw($sql));
            $sql = "SELECT `user_id`,`funnel_id`,`bank_id` FROM `logs` l WHERE `bank_id` = '" . $bank_id . "' AND `dateadd` " . $where_period . $where_user;
            $usr_bnk_fnl = DB::select(DB::raw($sql));
            $where_bank = " AND l.`bank_id` = " . (int) $bank_id;
        }

        $sql = "SELECT c.`inn`,c.`organizationName`,c.`fullName`,c.`phoneNumber`,f.`name`, b.name, c.`registration`, l.`dateadd` datecall, u.`fio` operator,c.`region`,c.`address`  FROM `logs` l LEFT JOIN `clients` c ON (c.`id` = l.`client_id`) LEFT JOIN `funnels` f ON (f.`id` = l.`funnel_id`) LEFT JOIN `banks` b ON (b.id = l.`bank_id`) LEFT JOIN `users` u ON (u.`id` = l.`user_id`) WHERE l.`dateadd` " . $where_period . $where_bank . $where_user;
        $report = DB::select(DB::raw($sql));

        $a_users_o = array_unique(array_column($usr_bnk_fnl, 'user_id'));
        $a_users = User::select('id', 'fio')->whereIn('id', $a_users_o)->get()->toArray();
        $a_funnels_o = array_unique(array_column($usr_bnk_fnl, 'funnel_id'));
        $a_funnels = Funnel::orderBy('order', 'asc')->whereIn('id', $a_funnels_o)->get()->toArray();

        $json = [];
        $json['header'] = array_merge([implode("  ", $period)], array_column($a_funnels, 'name', 'id'));
        $json['funnels'] = $a_funnels;
        $json['users'] = $a_users;
        $json['banks'] = $a_banks;
        $json['ubf'] = $usr_bnk_fnl;
        $json['all'] = $all_count[0]->COUNT;
        $json['report'] = $report;

        return response($json);
    }

    public function recall(Request $request)
    {
        $data = $request->All();
        $bank_id = $data['bank_id'];
        $user_id = $data['user_id'];
        $recall = $data['recall'];
        $client_id = $data['client_id'];
        $a_log = [];
        $a_log = ['client_id' => $client_id, 'user_id' => $user_id, 'bank_id' => $bank_id,  'other' => $recall, 'dateadd' => Now(), 'timeadd' => Now()];
        Log::insert($a_log);
        Client::where('id', $client_id)->update(['recall' => $recall]);
        return response("Время перезвона добавлено клиенту", 200);
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
