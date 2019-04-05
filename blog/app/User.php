<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    public $timestamps=false;
//    use Notifiable;
//
//
//    public function insertData($data)
//    {
//        try {
//            $result = DB::table('users')->insertGetId($data);
//            if ($result) {
//                return $result;
//            } else return 0;
//        } catch (\Exception $e) {
//            dd($e->getMessage());
//        }
//
//    }
}
