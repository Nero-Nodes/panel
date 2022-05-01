<?php

namespace Pterodactyl\Services\Servers;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Exceptions\DisplayException;

class EditServerService
{
    /**
     * Updates the requested instance with new limits.
     */
    public function handle(Request $request, Server $server)
    {
        $resource = $request['resource'];
        $value = $request['value'];
        $user = $request->user();

        if ($resource == 1) {

            if ($user->cr_cpu < 50) return throw new DisplayException('You do not have enough CPU to perform this action.');
            // We'll start by adding the resource to the server.
            $server->update([
                'cpu' => $server->cpu + $value,
            ]);

            // Then, we'll change the user's resource balance.
            $user->update([
                'cr_cpu' => $user->cr_cpu - $value,
            ]);
        };

        if ($resource == 2) {
            if ($user->cr_ram < 1024) return throw new DisplayException('You do not have enough RAM to perform this action.');
            // We'll start by adding the resource to the server.
            $server->update([
                'memory' => $server->memory + $value,
            ]);

            // Then, we'll change the user's resource balance.
            $user->update([
                'cr_ram' => $user->cr_ram - $value,
            ]);
        };

        if ($resource == 3) {
            if ($user->cr_storage < 1024) return throw new DisplayException('You do not have enough storage to perform this action.');
            // We'll start by adding the resource to the server.
            $server->update([
                'disk' => $server->disk + $value,
            ]);

            // Then, we'll change the user's resource balance.
            $user->update([
                'cr_storage' => $user->cr_storage - $value,
            ]);
        }
    }
}
