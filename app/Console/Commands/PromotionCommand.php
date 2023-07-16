<?php

namespace App\Console\Commands;

use App\Models\Promotion;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class PromotionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotion:schedule';

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
        Promotion::where('start_date', $today)->update([
            'active' => 1,
        ]);

        // promotion unactive
        Promotion::where('end_date', $today)->update([
            'active' => 0,
        ]);
    }
}
