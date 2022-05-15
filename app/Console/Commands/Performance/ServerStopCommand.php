<?php

namespace Pterodactyl\Console\Commands\Schedule;

use Exception;
use Pterodactyl\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Repositories\Wings\DaemonPowerRepository;

class ServerStopCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'p:performance:run';

    /**
     * @var string
     */
    protected $description = 'Shuts down all servers which have default resources.';

    /**
     * ServerStopCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Shuts down all servers which are using default resources.
     */
    protected function handle(Server $server, DaemonPowerRepository $powerRepository)
    {
        $servers = $server->where('renewable', true)->get();
        $this->output('Retrieved ' . $servers->count() . ' servers.', false);

        foreach ($servers as $svr) {
            if (
                // If the server uses the default limits, run.
                $svr->cpu <= 150 |
                $svr->memory <= 1536 |
                $svr->disk <= 5120
            ) {
                // Shut down the server instance.
                $powerRepository->setServer($svr)->send('stop');
                $this->output('Shut down server with ID: '.$svr->id, false);
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
            } catch (Exception $ex) { /* Do nothing */
            }
        }

        $this->line($message);
    }
}
