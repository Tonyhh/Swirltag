<?php

namespace App\Actions;

use App\Actions\ProcessHashtags;
use Illuminate\Http\Request;
use App\Models\Status;
use Illuminate\Support\Facades\Cache;

class StoreNewStatus
{
    public function handle(Request $request)
    {
        $userId = $request->user()->id;
        $body = $request->input('body');
        $parent_id = $request->input('status_id') ? $request->input('status_id') : null;

        // Lock creating new Statues for 5 seconds to prevent spam
        $lock = Cache::lock('user-posting:' . $userId, 5);

        if ($lock->get()) {
            // Process hashtags before creating the Status
            $processHashtags = new ProcessHashtags();
            $processHashtags($body);

            $statusData = [
                'body' => $body,
                'user_id' => $userId,
                'parent_id' => $parent_id,
            ];

            if ($request->filled('media_id')) {
                $statusData['media_id'] = $request->input('media_id');
            }

            // Create the Status
            $status = Status::create($statusData);

            // Always release the lock after the operation
            $lock->release();

            return $status;
        }
    }
}