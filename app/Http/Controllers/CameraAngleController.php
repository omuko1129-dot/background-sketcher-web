<?php

namespace App\Http\Controllers;

use App\Models\CameraAngle;
use Illuminate\Http\Request;

class CameraAngleController extends Controller
{
    /**
     * 新しい構図を保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'zoom' => 'required|numeric',
            'pitch' => 'required|numeric',
            'bearing' => 'required|numeric',
            'center' => 'required|array',
        ]);

        $request->user()->cameraAngles()->create($validated);

        return back()->with('message', '構図を保存しました');
    }

    /**
     * 構図を削除
     */
    public function destroy(CameraAngle $cameraAngle)
    {
        if ($cameraAngle->user_id !== auth()->id()) {
            abort(403);
        }

        $cameraAngle->delete();

        return back()->with('message', '構図を削除しました');
    }
}
