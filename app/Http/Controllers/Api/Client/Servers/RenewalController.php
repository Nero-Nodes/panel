<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Throwable;
use Pterodactyl\Models\User;
use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Exceptions\DisplayException;
use Illuminate\Validation\ValidationException;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

class RenewalController extends ClientApiController
{

    /**
     * RenewalController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws DisplayException
     * @throws Throwable
     * @throws ValidationException
     */
    public function renew(Request $request, Server $server)
    {
        if ($request->user()->cr_balance < 25) {
            throw new DisplayException('You do not have enough coins to renew this server.');
        }

        try {
            // Not working
            Server::where('uuid', $request['uuid'])->update([
                'renewal' => $request['current'] + 7,
            ]);
        } catch (DisplayException $e) {
            throw new DisplayException('There was an error while renewing your server. Please contact support.');
        }

        try {
            $user = $request->user()->id;
            $balance = User::select('cr_balance')->where('id', $user)->get();

            User::where('id', $user)->update([
                'cr_balance' => $request->user()->cr_balance - 25,
            ]);
        } catch (DisplayException $e) {
            throw new DisplayException('There was an error while removing coins from your account.');
        }
    }
}
