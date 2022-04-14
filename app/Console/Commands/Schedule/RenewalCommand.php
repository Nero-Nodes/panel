<?php

namespace Pterodactyl\Console\Commands\Schedule;

use Exception;
use Throwable;
use Pterodactyl\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Services\Servers\SuspensionService;
use Pterodactyl\Services\Servers\ServerDeletionService;

class ProcessRunnableCommand extends Command
{
    /**
     * @var \Pterodactyl\Services\Servers\SuspensionService
     */
    protected $suspensionService;

    /**
     * @var \Pterodactyl\Services\Servers\ServerDeletionService
     */
    protected $deletionService;

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
    public function __construct(SuspensionService $suspensionService, ServerDeletionService $deletionService)
    {
        parent::__construct();

        $this->suspensionService = $suspensionService;
        $this->deletionService = $deletionService;
    }

    /**
     * Handle command execution.
     */
    public function handle(Server $server)
    {
        Http::post(env('WEBHOOK_URL'), ['content' => 'Executing daily renewal script.']);

        $servers = $server->where('renewable', true)->get();

        if ($servers->count() < 1) {
            Http::post(env('WEBHOOK_URL'), ['content' => 'There are no scheduled tasks for servers that need to be run.']);
            $this->line('There are no scheduled tasks for servers that need to be run.');
            return;
        } else {
            Http::post(env('WEBHOOK_URL'), ['content' => 'Processing renewals for '.$servers->count().' servers.']);
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
        $this->line('Renewals completed successfully.');
        Http::post(env('WEBHOOK_URL'), ['content' => 'Renewals completed successfully.']);
    }

    /**
     * Takes one day off of the time a server has until it needs to be
     * renewed.
     */
    protected function process(Server $server)
    {
        $servers = $server->where('renewable', true)->get();

        foreach ($servers as $s) {
            $server->update(['renewal' => $s->renewal -1]);
            continue;
        }


        foreach ($servers as $s) {
            if ($s->renewal == 0 || $s->renewal < 0) {
                $this->suspensionService->toggle($s, 'suspend');
                continue;
            }
            if ($s->renewal == -7 || $s->renewal < -7) {
                $this->deletionService->handle($s);
                continue;
            }
        }
        
    }
}
