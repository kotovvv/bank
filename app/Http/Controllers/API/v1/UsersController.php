<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Log;
// use App\Models\Import;
use App\Models\Client;
use DB;
use Debugbar;
use Hash;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    // return User::All();
    return User::select(['users.*', DB::raw('(SELECT COUNT(user_id) FROM clients WHERE clients.user_id = users.id) as hmlids ')]) //

      // ->where('users.role_id', '>', 1)
      // ->where('users.active', 1)
      // ->leftJoin('lids', 'users.id', '=', 'lids.user_id')
      ->orderBy('users.role_id', 'asc')
      // ->groupBy('users.id')
      ->get();
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

    $data = $request->all();
    // Debugbar::info($data);
    if (isset($data['password'])) $password = Hash::make($data['password']);
    if (isset($data['id'])) {
      //  Debugbar::info('update');
      $arr = [];
      $arr['name'] = $data['name'];
    //   $arr['active'] = $data['active'];
      $arr['role_id'] = $data['role_id'];
      $arr['fio'] = $data['fio'];
      $arr['group_id'] = $data['group_id'];
      if(isset($data['bank_id'])){
          $arr['bank_id'] = $data['bank_id'];
      }
      if (User::where('id', $data['id'])->update($arr)) {
        if (isset($data['password'])) {
          $user = User::find($data['id']);
           $user->password = $password;
          $user->save();
        }
        return response('User updated', 200);
      } else return response('User updated error', 301);
    } else {
        $user = new User();
        $user->name = $data["name"];
        $user->fio = $data["fio"];
        $user->role_id = $data["role_id"];
        $user->password = $password;
        $user->save();
        return response('User added', 200);
    }
  }

  public function status_users(Request $request)
  {
    $repoprt = [];
    $req = $request->All();
    // Debugbar::info($req);
    $a_users = $req['users'];
    $datefrom = $req['datefrom'];
    $dateto = $req['dateto'];


    foreach ($a_users as $user_id) {
      $sql = "SELECT fio FROM `users` WHERE id = " . $user_id;
    }
    $fio = DB::select(DB::raw($sql));

    foreach ($a_users as $user_id) {
      $sql = "SELECT COUNT(*) n FROM `clients` WHERE `user_id` =  " . $user_id ;
    }
    $all = DB::select(DB::raw($sql));

    $current_date = $datefrom;
    // write dates

    $repoprt[] =  ['color' => '', 'col' => ['Пользователь', $fio[0]->fio]];
    $repoprt[] =['color' => '', 'col' => ['Всего лидов', $all[0]->n]];
    $row_dates = ['color' => '', 'col' => ['Даты']];
    $row_added = ['color' => '', 'col' => ['Добавлено']];
    $row_status = [];
    $count_status = 0;
    while (strtotime($current_date) <= strtotime($dateto)) {
      $row_dates['col'][] = $current_date;
      $sql = "SELECT COUNT(*) n FROM `clients` WHERE `user_id` = " . $a_users[0] . " AND CAST(created_at AS DATE) = '" . $current_date . "'";
      $new = DB::select(DB::raw($sql));
      $row_added['col'][] = $new[0]->n;

      //
      $current_date = date("Y-m-d", strtotime("+1 day", strtotime($current_date)));
    }

    $repoprt[] = $row_dates;
    $repoprt[] = $row_added;
    // Debugbar::info($new);
    $statuses = DB::select(DB::raw("SELECT id, `name`, color FROM `statuses` WHERE `active` = 1 ORDER BY `order` ASC"));

    foreach ($statuses as $status) {
      $current_date = $datefrom;
      $col = [$status->name];
      $count_status = 0;
       while (strtotime($current_date) <= strtotime($dateto)) {
         $sql = "SELECT COUNT(*) n FROM `logs` WHERE `user_id` = " . $a_users[0] . " AND CAST(created_at AS DATE) = '" . $current_date . "'  AND `status_id` = " . $status->id ;
         $sts = DB::select(DB::raw($sql));
         $col[] =  $sts[0]->n;
         $count_status += $sts[0]->n;
        //  Debugbar::info($sql);
      $current_date = date("Y-m-d", strtotime("+1 day", strtotime($current_date)));
       }
       $col[] = $count_status;
       $repoprt[] = ['color' => $status->color,'col'=> $col ];
    }
    // $repoprt[] = $row_status;

    // SELECT id, NAME,color FROM `statuses` WHERE `active` = 1 ORDER BY `order`
    // $statuses = DB::select(DB::raw("SELECT id, `name`, color FROM `statuses` WHERE `active` = 1 ORDER BY `order` ASC"));
    // foreach ($statuses as $status) {
    //   $sql = "SELECT '" . $status->color . "' color,'" . $status->name . "'  text ";
    //   if ($status->id == 8) {
    //     foreach ($a_users as $user_id) {
    //       $sql .= ",(SELECT COUNT(*) FROM `lids` WHERE `user_id` = " . $user_id . " AND DATE(`updated_at`) BETWEEN '" . $datefrom . "' AND '" . $dateto . "' AND `status_id` = 8) u" . $user_id;
    //     }
    //   } else {
    //     foreach ($a_users as $user_id) {
    //       $sql .= ",(SELECT COUNT(*) FROM `logs` WHERE `user_id` = " . $user_id . " AND DATE(`updated_at`) BETWEEN '" . $datefrom . "' AND '" . $dateto . "' AND `status_id` = " . $status->id . ") u" . $user_id;
    //     }
    //   }
    //   $sts = DB::select(DB::raw($sql));
    //   $repoprt['status'] =  $sts[0];
    // }
    return $repoprt;

  }


  public function getusers()
  {

    return User::select(['users.*', DB::raw('(SELECT COUNT(user_id) FROM users WHERE users.user_id = users.id) as hmlids ')])

      ->where('users.role_id', '>', 1)
      ->where('users.active', 1)
      // ->leftJoin('lids', 'users.id', '=', 'lids.user_id')
      ->orderBy('users.role_id', 'asc')
      // ->groupBy('users.id')
      ->get();
  }
  public function getrelatedusers(Request $request)
  {
    $related_users = $request->All();
    if (count($related_users) == 0) return false;

    return User::select(['users.*', DB::raw('(SELECT COUNT(user_id) FROM clients WHERE clients.user_id = users.id) as hmlids ')])

      ->where('users.role_id', '>', 1)
      ->where('users.active', 1)
      ->whereIn('id', $related_users)
      // ->leftJoin('lids', 'users.id', '=', 'lids.user_id')
      ->orderBy('users.role_id', 'asc')
      // ->groupBy('users.id')
      ->get();
  }

  public function getroles()
  {
    return Role::All();
  }

  public function deleteuser($id)
  {
    Client::where('user_id', '=', $id)->delete();
    Log::where('user_id', '=', $id)->delete();
    // Import::where('user_id', '=', $id)->delete();
    $user = User::find($id);
    $user->delete();
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
