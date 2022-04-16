<?php

namespace Pterodactyl\Console\Commands\Schedule;

use Exception;
use Throwable;
use Pterodactyl\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Collection;
use Pterodactyl\Services\Servers\SuspensionService;
use Pterodactyl\Services\Servers\ServerDeletionService;

class RenewalCommand extends Command
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
        $this->output('Executing daily renewal script.', false);    
        $this->process($server);
        $this->output('Renewals completed successfully.', true);
    }

    /**
     * Takes one day off of the time a server has until it needs to be
     * renewed.
     */
    protected function process(Server $server)
    {
        $servers = $server->where('renewable', true)->get();
        $this->output('Processing renewals for '.$servers->count().' servers.', true);

        foreach ($servers as $svr) {
            $this->output('Renewing server '.$svr->name, false);
            $svr->update(['renewal' => $svr->renewal - 1]);

            if ($svr->renewal == 0 || $svr->renewal < 0) {
                $this->output('Suspending server '.$svr->name, false);
                $this->suspensionService->toggle($svr, 'suspend');
            }

            if ($svr->renewal == -7 || $svr->renewal < -7) {
                $this->output('Deleting'.$svr->name, false);
                $this->deletionService->handle($svr);
            }
        };
    }

    protected function output(string $message, bool $webhook)
    {
        if (!$message) return $this->line('empty line');
        if (!env('WEBHOOK_URL')) return $this->line('No webhook URL specified, unable to send.');

        if ($webhook == true) {
            try {
                Http::post(env('WEBHOOK_URL'), ['content' => $message]);
            } catch (Exception $ex) { /* Do nothing */ }
        }

        $this->line($message);
    }
}
