<?php

namespace Pterodactyl\Console\Commands\Schedule;

use Exception;
use Throwable;
use Pterodactyl\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Services\Servers\SuspensionService;

class ProcessRunnableCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'p:schedule:renewal';

    /**
     * @var string
     */
    protected $description = 'Process renewals for servers.';

    /**
     * Handle command execution.
     */
    public function handle()
    {
        $servers = Server::query()
            ->where('renewable', true)
            ->where('renewal', '>', 0)
            ->get();

        if ($servers->count() < 1) {
            $this->line('There are no scheduled tasks for servers that need to be run.');
            return;
        } else {
            $this->line('Processing renewals for '.$servers->count().' servers.');
        }

        $bar = $this->output->createProgressBar(count($servers));
        foreach ($servers as $s) {
            $bar->clear();
            $this->process($s);
            $bar->advance();
            $bar->display();
        }

        $this->line('');
    }

    /**
     * Takes one day off of the time a server has until it needs to be
     * renewed.
     */
    protected function process()
    {
        $servers = Server::where('renewable', true)->get();

        foreach ($servers as $s) {
            if ($s->renewal = 0) {
                // Currently not working, need to look into this
                SuspensionService::toggle($s, 'suspend');
            }
        }

        foreach ($servers as $s) {
            DB::table('servers')->where('renewable', true)->update([
                'renewal' => $s->renewal - 1,
            ]);
        }
    }
}
