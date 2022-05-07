<?php

namespace Pterodactyl\Console\Commands\Schedule;

use Exception;
use Throwable;
use Pterodactyl\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Collection;
use Pterodactyl\Services\Servers\ServerDeletionService;

class ServerCheckCommand extends Command
{

    /**
     * @var \Pterodactyl\Services\Servers\ServerDeletionService
     */
    protected $deletionService;

    /**
     * @var string
     */
    protected $signature = 'p:schedule:check';

    /**
     * @var string
     */
    protected $description = 'Process checks for servers.';

    /**
     * ServerCheckCommand constructor.
     */
    public function __construct(ServerDeletionService $deletionService)
    {
        parent::__construct();
        $this->deletionService = $deletionService;
    }

    /**
     * Handle command execution.
     */
    public function handle(Server $server)
    {
        $this->output('Executing minutely server integrity check.', false);    
        $this->process($server);
        $this->output('Server integrity checks complete.', false);
    }

    /**
     * Takes one day off of the time a server has until it needs to be
     * renewed.
     */
    protected function process(Server $server)
    {
        $servers = $server->where('renewable', true)->get();
        $this->output('Checking resource usage of '.$servers->count().' servers.', true);

        foreach ($servers as $svr) {
            if (
                $server->cpu > 400 |
                $server->memory > 16384 |
                $server->disk > 65536
            ) {
                $this->deletionService->handle($svr);
                $user = DB::table('users')->where('id', $svr->owner->id)->get();

                $this->output(
                    '<@623534693295325196> and <@298527677394976789> - '.
                    '\nServer has not passed the integrity check and has been force deleted.'.
                    '\nServer ID: '.$svr->id.
                    '\nOwner ID:'.$user->id.
                    '\nOwner Discord: '.$user->name_first.'#'.$user->name_last.
                    '\nOwner IPv4: '.$user->ip_address
                , true);
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
