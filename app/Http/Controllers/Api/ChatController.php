<?php

namespace App\Http\Controllers\Api;

use App\Entities\Friend;
use App\Entities\Message;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller {

    public function sendMessage(Request $request) {
        $input = $request->input();

        $message = new Message;
        $message->from = $input['from'];
        $message->to = $input['to'];
        $message->type = 'text';
        $message->message = $input['message'];

        $message->save();

        return response()->json([
            'success' => true,
        ]);
    }

    public function loadMessages(Request $request) {
        $input = $request->input();

        $messages = Message::where([['from', $input['user1']], ['to', $input['user2']]])
            ->orWhere([['from', $input['user2']], ['to', $input['user1']]])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

}