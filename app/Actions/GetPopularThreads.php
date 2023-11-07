<?php
namespace App\Actions;

use App\Models\Thread;
use Illuminate\Support\Collection;

class GetPopularThreads {

    public function handle(): Collection
    {
        $threads = Thread::with(['user', 'media'])->orderBy('view_count', 'desc')->limit(10)->get();

        return $threads;
    }
}
