<?php

namespace Pterodactyl\Console\Commands\Schedule;

use Exception;
use Pterodactyl\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Repositories\Wings\DaemonPowerRepository;
use Pterodactyl\Repositories\Wings\DaemonCommandRepository;
use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;

class ServerStopCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'p:schedule:stop';

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
    public function handle(Server $server, DaemonPowerRepository $powerRepository, DaemonCommandRepository $commandRepository)
    {
        $this->output('Waiting 10 seconds...', false);
        sleep(10);

        $servers = $server->where('renewable', true)->get();
        $this->output('Retrieved ' . $servers->count() . ' servers.', false);

        foreach ($servers as $svr) {
            if (
                // If the server uses the default limits, run.
                $svr->cpu <= 150 &&
                $svr->memory <= 1536 &&
                $svr->disk <= 5120
            ) {
                // Log to the console when a server has been detected as having
                // only the default limits allocated to it.
                $this->output($svr->id.' | Detected as having '.$svr->cpu.'% CPU, '.$svr->memory.'MB RAM, '.$svr->disk.'MB disk.', false);

                // Send a message to the console which will show up in Minecraft
                // servers which informs the user of the scheduled shutdown.
                try {
                    $commandRepository->setServer($svr)->send('
                        say This server is being shut down due to you using the Free Tier, meaning you haven\'t
                        upgraded your server instance. You can restart your server at any time. Please consider upgrading your
                        instance via the Store on the control panel.
                    ');
                } catch (DaemonConnectionException $exception) {
                    $this->output($svr->id.' | ERR | '.$exception, false);
                }

                // Sleep for 5 seconds to allow for users to read message.
                $this->output($svr->id.' | Waiting for 5 seconds until shutdown...', false);
                sleep(5);

                try {
                    // Shut down the server instance.
                    $powerRepository->setServer($svr)->send('stop');
                    $this->output($svr->id . ' | Shutdown success, looping to next server.', false);
                } catch (DaemonConnectionException $exception) {
                    // Report an error to the console when server cannot be shutdown.
                    $this->output($svr->id.' | ERR | '.$exception, false);
                }
            }
        };

        $this->output('All servers with default resource levels or lower have been shutdown successfully.', false);
    }

    protected function output(string $message, bool $webhook)
    {
        if (!$message) return $this->line('empty line');

        $this->line($message);

        if (!env('WEBHOOK_URL')) return $this->line('No webhook URL specified, unable to send.');

        if ($webhook == true) {
            try {
                Http::post(env('WEBHOOK_URL'), ['content' => $message]);
            } catch (Exception $ex) { /* Do nothing */ }
        }
    }
}
