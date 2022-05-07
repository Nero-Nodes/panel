<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Credits;

use Illuminate\Support\Facades\DB;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Http\Requests\Api\Client\StoreRequest;
use Pterodactyl\Contracts\Repository\CreditsRepositoryInterface;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

class ResourceController extends ClientApiController
{
    public CreditsRepositoryInterface $credits;

    public function __construct(CreditsRepositoryInterface $credits)
    {
        parent::__construct();
        $this->credits = $credits;
    }

    /**
     * @throws DisplayException
     */
    public function buySlots(StoreRequest $request): array
    {
        $user_resources = DB::table('users')->select(['cr_balance', 'cr_slots'])->where('id', '=', $request->user()->id)->get()[0];
        $cost = $this->credits->get('store:slots_cost', 100);

        if ($request->user()->cr_slots > 0) {
            return throw new DisplayException('You cannot have more than one server slot in the store at a time.');
        }

        if ($user_resources->cr_balance < $cost) {
            return throw new DisplayException('You don\'t have enough credits to purchase this resource.');
        }

        DB::table('users')->where('id', '=', $request->user()->id)->update(['cr_slots' => $user_resources->cr_slots + 1, 'cr_balance' => $user_resources->cr_balance - $cost]);

        return [
            'success' => true,
            'data' => []
        ];
    }

    /**
     * @throws DisplayException
     */
    public function buyCPU(StoreRequest $request): array
    {
        $user_resources = DB::table('users')->select(['cr_balance', 'cr_cpu'])->where('id', '=', $request->user()->id)->get()[0];
        $cost = $this->credits->get('store:cpu_cost', 20);

        if ($request->user()->cr_cpu > 250) {
            return throw new DisplayException('You cannot have more than 300% CPU in the store at a time.');
        }

        if ($user_resources->cr_balance < $cost) {
            return throw new DisplayException('You don\'t have enough credits to purchase this resource.');
        }

        DB::table('users')->where('id', '=', $request->user()->id)->update(['cr_cpu' => $user_resources->cr_cpu + 50, 'cr_balance' => $user_resources->cr_balance - $cost]);

        return [
            'success' => true,
            'data' => []
        ];
    }

    /**
     * @throws DisplayException
     */
    public function buyRAM(StoreRequest $request): array
    {
        $user_resources = DB::table('users')->select(['cr_balance', 'cr_ram'])->where('id', '=', $request->user()->id)->get()[0];
        $cost = $this->credits->get('store:ram_cost', 10);

        if ($request->user()->cr_ram > 7168) {
            return throw new DisplayException('You cannot have more than 8GB RAM in the store at a time.');
        }

        if ($user_resources->cr_balance < $cost) {
            throw new DisplayException('You don\'t have enough credits to purchase this resource.');
        }

        DB::table('users')->where('id', '=', $request->user()->id)->update(['cr_ram' => $user_resources->cr_ram + 1024, 'cr_balance' => $user_resources->cr_balance - $cost]);

        return [
            'success' => true,
            'data' => []
        ];
    }

    /**
     * @throws DisplayException
     */
    public function buyStorage(StoreRequest $request): array
    {
        $user_resources = DB::table('users')->select(['cr_balance', 'cr_storage'])->where('id', '=', $request->user()->id)->get()[0];
        $cost = $this->credits->get('store:storage_cost', 5);

        if ($request->user()->cr_storage > 15360) {
            return throw new DisplayException('You cannot have more than 16GB Storage in the store at a time.');
        }

        if ($user_resources->cr_balance < $cost) {
            throw new DisplayException('You don\'t have enough credits to purchase this resource.');
        }

        DB::table('users')->where('id', '=', $request->user()->id)->update(['cr_storage' => $user_resources->cr_storage + 1024, 'cr_balance' => $user_resources->cr_balance - $cost]);

        return [
            'success' => true,
            'data' => []
        ];
    }
}
