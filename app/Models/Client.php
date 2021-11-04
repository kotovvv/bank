<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Debugbar;


class Client extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = [
        'inn', 'fullName', 'phoneNumber', 'organizationName', 'address', 'region', 'registration', 'initiator', 'bank_id', 'operator_id', 'funnel_id', 'date_added'
    ];


    public static function toArrayBanksFunnels($client_id)
    {
        $banksfunnels = Client::where('id', $client_id)->value('banksfunnels');
    $banksfunnels = str_replace('"','',$banksfunnels);
        $a_banksfunnels = [];
        if (strstr($banksfunnels, ',') || strstr($banksfunnels, ':')) {
            if (strstr($banksfunnels, ',')) { //many banks
                $a_bfs = explode(',', $banksfunnels);
                foreach ($a_bfs as $bf) {
                        $a_bf = explode(':', $bf);
                        $a_banksfunnels[$a_bf[0]] = $a_bf[1];
                }
            } else { //one bank
                $a_bf = explode(':', $banksfunnels);
                $a_banksfunnels[$a_bf[0]] = $a_bf[1];
            }
        }
        return $a_banksfunnels;
    }


    public static function toStrArrayBanksFunnels($a_bankfunnel)
    {
        $str = '';
        if (is_array($a_bankfunnel)) {
            $a_str = [];
            foreach ($a_bankfunnel as $bank => $funnel) {
                $a_str[] = '"'.$bank . ':' . $funnel.'"';
            }
            $str = implode(',', $a_str);
        }
        return $str;
    }


    public static function setBankFunnels($a_clients, $bank_id, $funnel)
    {
        if (is_array($a_clients)) {
            $i = 0;
            $all = count($a_clients);
            foreach ($a_clients as $client_id) {
                $a_bankfunnel = self::toArrayBanksFunnels($client_id);

                if (array_key_exists($bank_id, $a_bankfunnel)) {
                    if ($a_bankfunnel[$bank_id] == $funnel) continue;
                }
                $a_bankfunnel[$bank_id] = $funnel;
                Client::where('id', $client_id)->update(['banksfunnels' => self::toStrArrayBanksFunnels($a_bankfunnel)]);
                $i++;
            }
            return ['all' => $all, 'done' => $i];
        }
        return false;
    }


    public static function delBankFromClients($a_clients, $bank_id)
    {
        if (is_array($a_clients)) {
            $i = 0;
            $all = count($a_clients);
            foreach ($a_clients as $client_id) {
                $a_bankfunnel = self::toArrayBanksFunnels($client_id);
                if (array_key_exists($bank_id, $a_bankfunnel)) {
                    unset($a_bankfunnel[$bank_id]);
                }else{
                    continue;
                }
                Client::where('id', $client_id)->update(['banksfunnels' => self::toStrArrayBanksFunnels($a_bankfunnel)]);
                $i++;
            }
            return ['all' => $all, 'done' => $i];
        }
        return false;
    }
}
