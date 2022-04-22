<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Throwable;
use Pterodactyl\Models\User;
use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Exceptions\DisplayException;
use Illuminate\Validation\ValidationException;
use Pterodactyl\Services\Servers\SuspensionService;
use Pterodactyl\Contracts\Repository\CreditsRepositoryInterface;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

class RenewalController extends ClientApiController
{
    private CreditsRepositoryInterface $credits;
    protected SuspensionService $suspensionService;

    /**
     * RenewalController constructor.
     */
    public function __construct(
        CreditsRepositoryInterface $credits,
        SuspensionService $suspensionService
    )
    {
        parent::__construct();

        $this->credits = $credits;
        $this->suspensionService = $suspensionService;
    }

    /**
     * @throws DisplayException
     * @throws Throwable
     * @throws ValidationException
     */
    public function renew(Request $request, Server $server)
    {
        $user = $request->user;
        $renewal = $this->credits->get('store:renewal_cost');

        if ($user->cr_balance < $renewal) {
            throw new DisplayException('You do not have enough coins to renew this server.');
        }

        try {
            Server::where('uuid', $request['uuid'])->update([
                'renewal' => $request['current'] + 7,
            ]);

            User::where('id', $user->id)->update([
                'cr_balance' => $user->cr_balance - $renewal,
            ]);
        } catch (DisplayException $e) {
            throw new DisplayException('There was an error while renewing your server. Please contact support.');
        }

        if ($server->status === 'suspended') {
            if (($request['current'] + 7) < 0) {
                return;
            } else {
                $this->suspensionService->toggle($server, 'unsuspend');
            };
        };

    }
}
