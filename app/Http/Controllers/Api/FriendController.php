<?php

namespace App\Http\Controllers\Api;

use App\Entities\Friend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FriendController extends Controller {

    public function addFriend(Request $request) {
        $input = $request->input();

        $count = Friend::where('user1', $input['user1'])->where('user2', $input['user2'])->count();
        if ($count > 0) {
            return response()->json(['success' => false, 'error' => 'exist']);
        }

        $count = Friend::where('user2', $input['user1'])->where('user1', $input['user2'])->count();
        if ($count > 0) {
            return response()->json(['success' => false, 'error' => 'exist']);
        }

        $friend = new Friend;

        $friend->user1 = $input['user1'];
        $friend->user1_name = $input['user1_name'];
        $friend->user2 = $input['user2'];
        $friend->user2_name = $input['user2_name'];

        $friend->save();

        return response()->json([
            'success' => true,
        ]);
    }

    public function getFriendList(Request $request) {
        $input = $request->input();

        $email = $input['email'];
        $friends = array();

        $tmp = Friend::where('user1',$email)->get();
        foreach($tmp as &$item) {
            array_push($friends, ['email' => $item['user2'], 'name' => $item['user2_name'],  'badge' => 0]);
        }

        $tmp = Friend::where('user2',$email)->get();
        foreach($tmp as &$item) {
            array_push($friends, ['email' => $item['user1'], 'name' => $item['user1_name'], 'badge' => 0]);
        }


        return response()->json([
            'success' => true,
            'friends' => $friends,
        ]);
    }


}