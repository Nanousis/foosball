<?php

namespace App\Http\Controllers;

use App\Models\Players;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    //
    function registerUser(Request $request)
    {
        $incomingFields = $request->validate([
            'name' => 'required|min:3|max:20',
            'avatar' => 'nullable|image|max:2048' // <-- expects image, max 2MB
        ]);
        $incomingFields['name']= strip_tags($incomingFields['name']);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $incomingFields['avatar'] = $avatarPath;
        }
        Players::create($incomingFields);
        return redirect()->back()->with('success', 'Player created successfully.');
    }
    public function deleteUser($id)
    {
        $player = Players::findOrFail($id);

        // Delete avatar file if it exists
        if ($player->avatar && Storage::disk('public')->exists($player->avatar)) {
            Storage::disk('public')->delete($player->avatar);
        }

        $player->delete();

        return redirect()->back()->with('success', 'Player deleted successfully.');
    }

}
