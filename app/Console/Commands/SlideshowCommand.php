<?php

namespace App\Console\Commands;

use App\Models\Slideshow;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class SlideshowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slideshow:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');

        // promotion active
        Slideshow::where('start_date', $today)->update([
            'active' => 1,
        ]);

        // promotion unactive
        Slideshow::where('end_date', $today)->update([
            'active' => 0,
        ]);
    }
}
