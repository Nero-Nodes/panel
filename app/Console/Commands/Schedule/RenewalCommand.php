<?php

namespace Pterodactyl\Console\Commands\Schedule;

use Exception;
use Throwable;
use Pterodactyl\Models\Server;
use Illuminate\Console\Command;
use Pterodactyl\Services\Servers\SuspensionService;

class ProcessRunnableCommand extends Command
{
    /**
     * @var \Pterodactyl\Services\Servers\SuspensionService
     */
    protected $suspensionService;

    /**
     * @var string
     */
    protected $signature = 'p:schedule:renewal';

    /**
     * @var string
     */
    protected $description = 'Process renewals for servers.';

    /**
     * DeleteUserCommand constructor.
     */
    public function __construct(SuspensionService $suspensionService)
    {
        parent::__construct();

        $this->suspensionService = $suspensionService;
    }

    /**
     * Handle command execution.
     */
    public function handle(Server $server)
    {
        $servers = $server->where('renewable', true)->get();

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
    protected function process(Server $server)
    {
        $servers = $server->where('renewable', true)->get();


        // Testing area
        foreach ($servers as $s) {
            echo($s); // Works
            echo($s->id); // Doesn't work
        }

        foreach ($servers as $s) {
            if ($s->renewal = 0 || $s->renewal < 0) {
                $this->suspensionService->toggle($s, 'suspend');
            }
        }

        foreach ($servers as $s) {
            // $s->renewal is being read as 0 here.
            // Needs fixing!!
            $server->update(['renewal' => $s->renewal -1]);
        }
    }
}
