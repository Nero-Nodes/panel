<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Throwable;
use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Exceptions\DisplayException;
use Illuminate\Validation\ValidationException;
use Pterodactyl\Http\Requests\Api\Client\Servers\RenewalRequest;
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
    public function index(Request $request, Server $server)
    {
        if ($request->user()->cr_balance < 25) {
            throw new DisplayException('You do not have enough coins to renew this server.');
        }

        /*
            $balance = DB::table('users')->select('cr_balance')->where('id', '=', $id)->get();

            DB::table('users')->where('id', '=', $id)->update([
                'cr_balance' => $balance - 25,
            ]);
        */

        try {
            // Not working
            DB::table('servers')->where('id', $server->id)->update([
                'renewal' => $server->renewal + 7,
            ]);
        } catch (DisplayException $e) {
            throw new DisplayException('There was an error while renewing your server. Please contact support.');
        }
    }
}
