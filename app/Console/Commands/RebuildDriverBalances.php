<?php

namespace App\Console\Commands;

use App\Models\DriverBalanceSummary;
use Illuminate\Console\Command;

class RebuildDriverBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:rebuild {--driver= : 指定駕駛 ID，不指定則重建全部}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重建駕駛餘額彙總表';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('開始重建駕駛餘額彙總...');

        if ($driverId = $this->option('driver')) {
            DriverBalanceSummary::updateBalance((int) $driverId);
            $this->info("駕駛 ID {$driverId} 的餘額已更新");
        } else {
            DriverBalanceSummary::rebuildAll();
            $this->info('所有駕駛餘額已重建完成');
        }

        return Command::SUCCESS;
    }
}
