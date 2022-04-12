<?php

namespace Pterodactyl\Console\Commands\Schedule;

use Exception;
use Throwable;
use Illuminate\Console\Command;
use Pterodactyl\Models\Server;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Services\Schedules\ProcessScheduleService;

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
        }

        $bar = $this->output->createProgressBar(count($serevrs));
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
    protected function process(Server $server)
    {
        foreach ($server as $s) {
            DB::table('servers')->where('id', '=', $s->id)->update([
                'renewal' => $s->renewal -1,
            ]);
        }
    }
}
