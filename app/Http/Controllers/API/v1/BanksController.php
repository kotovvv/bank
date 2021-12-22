<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bank;
use Barryvdh\Debugbar\Twig\Extension\Debug;
use Illuminate\Support\Facades\Http;
use DB;
use Debugbar;
use DebugBar\DebugBar as DebugBarDebugBar;

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


    public function getRegions(Request $request)
    {
        $data = $request->All();
        $response =
            json_decode('[
        {
            "id": 99,
            "name": "Адыгея Республика",
            "code": 1,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 100,
            "name": "Алтай Республика",
            "code": 4,
            "utc_offset": 7,
            "is_deleted": false
        },
        {
            "id": 101,
            "name": "Алтайский Край",
            "code": 22,
            "utc_offset": 7,
            "is_deleted": false
        },
        {
            "id": 102,
            "name": "Амурская Область",
            "code": 28,
            "utc_offset": 9,
            "is_deleted": false
        },
        {
            "id": 103,
            "name": "Архангельская Область",
            "code": 29,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 104,
            "name": "Астраханская Область",
            "code": 30,
            "utc_offset": 4,
            "is_deleted": false
        },
        {
            "id": 105,
            "name": "Башкортостан Республика",
            "code": 2,
            "utc_offset": 5,
            "is_deleted": false
        },
        {
            "id": 106,
            "name": "Белгородская Область",
            "code": 31,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 107,
            "name": "Брянская Область",
            "code": 32,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 108,
            "name": "Бурятия Республика",
            "code": 3,
            "utc_offset": 8,
            "is_deleted": false
        },
        {
            "id": 109,
            "name": "Владимирская Область",
            "code": 33,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 110,
            "name": "Волгоградская Область",
            "code": 34,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 111,
            "name": "Вологодская Область",
            "code": 35,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 112,
            "name": "Воронежская Область",
            "code": 36,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 113,
            "name": "Дагестан Республика",
            "code": 5,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 114,
            "name": "Еврейская Автономная Область",
            "code": null,
            "utc_offset": 10,
            "is_deleted": false
        },
        {
            "id": 115,
            "name": "Забайкальский Край",
            "code": 75,
            "utc_offset": 9,
            "is_deleted": false
        },
        {
            "id": 116,
            "name": "Ивановская Область",
            "code": 37,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 117,
            "name": "Ингушетия Республика",
            "code": 6,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 118,
            "name": "Иркутская Область",
            "code": 38,
            "utc_offset": 8,
            "is_deleted": false
        },
        {
            "id": 119,
            "name": "Кабардино-Балкарская Республика",
            "code": 7,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 120,
            "name": "Калининградская Область",
            "code": 39,
            "utc_offset": 2,
            "is_deleted": false
        },
        {
            "id": 121,
            "name": "Калмыкия Республика",
            "code": 8,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 122,
            "name": "Калужская Область",
            "code": 40,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 124,
            "name": "Камчатский Край",
            "code": 41,
            "utc_offset": 12,
            "is_deleted": false
        },
        {
            "id": 125,
            "name": "Карачаево-Черкесская Республика",
            "code": 9,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 126,
            "name": "Карелия Республика",
            "code": 10,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 127,
            "name": "Кемеровская Область",
            "code": 42,
            "utc_offset": 7,
            "is_deleted": false
        },
        {
            "id": 128,
            "name": "Кировская Область",
            "code": 43,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 129,
            "name": "Коми Республика",
            "code": 11,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 130,
            "name": "Костромская Область",
            "code": 44,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 131,
            "name": "Краснодарский Край",
            "code": 23,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 132,
            "name": "Красноярский Край",
            "code": 24,
            "utc_offset": 7,
            "is_deleted": false
        },
        {
            "id": 134,
            "name": "Курганская Область",
            "code": 45,
            "utc_offset": 5,
            "is_deleted": false
        },
        {
            "id": 135,
            "name": "Курская Область",
            "code": 46,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 136,
            "name": "Ленинградская Область",
            "code": 47,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 137,
            "name": "Липецкая Область",
            "code": 48,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 139,
            "name": "Магаданская Область",
            "code": 49,
            "utc_offset": 11,
            "is_deleted": false
        },
        {
            "id": 140,
            "name": "Марий Эл Республика",
            "code": 12,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 141,
            "name": "Мордовия Республика",
            "code": 13,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 142,
            "name": "Москва Город",
            "code": 77,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 143,
            "name": "Московская Область",
            "code": 50,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 144,
            "name": "Мурманская Область",
            "code": 51,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 145,
            "name": "Ненецкий Автономный округ",
            "code": 83,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 146,
            "name": "Нижегородская Область",
            "code": 52,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 147,
            "name": "Новгородская Область",
            "code": 53,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 148,
            "name": "Новосибирская Область",
            "code": 54,
            "utc_offset": 7,
            "is_deleted": false
        },
        {
            "id": 149,
            "name": "Омская Область",
            "code": 55,
            "utc_offset": 6,
            "is_deleted": false
        },
        {
            "id": 150,
            "name": "Оренбургская Область",
            "code": 56,
            "utc_offset": 5,
            "is_deleted": false
        },
        {
            "id": 151,
            "name": "Орловская Область",
            "code": 57,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 152,
            "name": "Пензенская Область",
            "code": 58,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 154,
            "name": "Пермский Край",
            "code": 59,
            "utc_offset": 5,
            "is_deleted": false
        },
        {
            "id": 155,
            "name": "Приморский Край",
            "code": 25,
            "utc_offset": 10,
            "is_deleted": false
        },
        {
            "id": 156,
            "name": "Псковская Область",
            "code": 60,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 157,
            "name": "Ростовская Область",
            "code": 61,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 158,
            "name": "Рязанская Область",
            "code": 62,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 159,
            "name": "Самарская Область",
            "code": 63,
            "utc_offset": 4,
            "is_deleted": false
        },
        {
            "id": 160,
            "name": "Санкт-Петербург Город",
            "code": 78,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 161,
            "name": "Саратовская Область",
            "code": 64,
            "utc_offset": 4,
            "is_deleted": false
        },
        {
            "id": 162,
            "name": "Сахалинская Область",
            "code": 65,
            "utc_offset": 11,
            "is_deleted": false
        },
        {
            "id": 163,
            "name": "Саха (Якутия) Республика",
            "code": 14,
            "utc_offset": 9,
            "is_deleted": false
        },
        {
            "id": 164,
            "name": "Свердловская Область",
            "code": 66,
            "utc_offset": 5,
            "is_deleted": false
        },
        {
            "id": 166,
            "name": "Северная Осетия - Алания Республика",
            "code": 15,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 167,
            "name": "Смоленская Область",
            "code": 67,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 168,
            "name": "Ставропольский Край",
            "code": 26,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 169,
            "name": "Тамбовская Область",
            "code": 68,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 170,
            "name": "Татарстан Республика",
            "code": 16,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 171,
            "name": "Тверская Область",
            "code": 69,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 172,
            "name": "Томская Область",
            "code": 70,
            "utc_offset": 7,
            "is_deleted": false
        },
        {
            "id": 173,
            "name": "Тульская Область",
            "code": 71,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 174,
            "name": "Тыва Республика",
            "code": 17,
            "utc_offset": 7,
            "is_deleted": false
        },
        {
            "id": 175,
            "name": "Тюменская Область",
            "code": 72,
            "utc_offset": 5,
            "is_deleted": false
        },
        {
            "id": 176,
            "name": "Удмуртия Республика",
            "code": null,
            "utc_offset": 4,
            "is_deleted": false
        },
        {
            "id": 177,
            "name": "Ульяновская Область",
            "code": 73,
            "utc_offset": 4,
            "is_deleted": false
        },
        {
            "id": 178,
            "name": "Хабаровский Край",
            "code": 27,
            "utc_offset": 10,
            "is_deleted": false
        },
        {
            "id": 179,
            "name": "Хакасия Республика",
            "code": 19,
            "utc_offset": 7,
            "is_deleted": false
        },
        {
            "id": 180,
            "name": "Ханты-Мансийский Автономный округ - Югра",
            "code": null,
            "utc_offset": 5,
            "is_deleted": false
        },
        {
            "id": 181,
            "name": "Челябинская Область",
            "code": 74,
            "utc_offset": 5,
            "is_deleted": false
        },
        {
            "id": 182,
            "name": "Чеченская Республика",
            "code": 20,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 184,
            "name": "Чувашская Республика - Чувашия",
            "code": 21,
            "utc_offset": 3,
            "is_deleted": false
        },
        {
            "id": 185,
            "name": "Чукотский Автономный округ",
            "code": 87,
            "utc_offset": 12,
            "is_deleted": false
        },
        {
            "id": 186,
            "name": "Ямало-Ненецкий Автономный округ",
            "code": 89,
            "utc_offset": 5,
            "is_deleted": false
        },
        {
            "id": 187,
            "name": "Ярославская Область",
            "code": 76,
            "utc_offset": 3,
            "is_deleted": false
        }
    ]');
        return response($response);
    }


    public function getCities(Request $request)
    {
        $data = $request->All();
        $bank_id = $data['bank_id'];
        $region_id = $data['region_id'];
        $query = '';
        if (isset($data['query'])) $query = '&query=' . $query;
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

    public function getDepartments(Request $request)
    {
        $data = $request->All();
        $bank_id = $data['bank_id'];
        $region_id = $data['region_id'];
        $city_id = $data['city_id'];

        $a_bank_actions = [
            'getDepartmets' => '/api/v1/sber_mq/merchant_branch?with_merchant_branches=1&region_id=' . $region_id . '&city_id=' . $city_id,
        ];

        $bank = Bank::where('id', $bank_id)->first();
        $action = $a_bank_actions['getDepartmets'];

        $response = Http::withHeaders([
            'Authorization' => 'Token token=' . $bank['token'],
            'Content-Type' => 'application/json'
        ])->get($bank['url'] . $action);

        $a_ans = $response->json();
        $entries = $response->object()->entries;

        $hm_page = ceil($a_ans['total_entries'] / $a_ans['per_page']);
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
