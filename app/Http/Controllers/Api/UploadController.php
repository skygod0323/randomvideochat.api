<?php

namespace App\Http\Controllers\Api;

use App\Entities\Profile;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request) {
        $dir = date('Y-m-d');

        $path = $request->file('file')->store('attachment/' . $dir, 'public');

        return response()->json([
            'success' => true,
            'url' => Storage::url($path)
        ]);
    }

    public function imageContent(Request $request) {
        $image_data = $request->input('image');
        $image_data = str_replace('data:image/png;base64,', '', $image_data);
        $image_data = str_replace('data:image/jpeg;base64,', '', $image_data);
        $image_data = str_replace(' ', '+', $image_data);

        $dir = date('Y-m-d');

        list($usec, $sec) = explode(".", microtime(true));
        $file = $usec.$sec;

        \Storage::disk('public')->put("/images/".$dir."/".$file.".jpg", base64_decode($image_data));
        
        $url = \Storage::disk('public')->url("/images/".$dir."/".$file.".jpg");

        $user = Auth::user();

        User::where('id', $user->id)->update(['photo' => $url]);

        return response()->json(array(
            'success' => true,
            'url' => $url
        ));
    }
}
