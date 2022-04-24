<?php

namespace App\Http\Controllers\Auth;

use Ramsey\Uuid\Uuid;
use Pterodactyl\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Contracts\Hashing\Hasher;

class DiscordLoginController extends AbstractLoginController
{
    private Hasher $hasher;

    /**
     * DiscordLoginController constructor.
     */
    public function __construct(Hasher $hasher) 
    {
        $this->hasher = $hasher;
    }

    public function redirect(): JsonResponse
    {
        Socialite::driver('discord')->redirect();
    }

    public function callback(): JsonResponse
    {
        $user = Socialite::driver('discord')->user();

        $data = [
            'uuid' => Uuid::uuid4()->toString(),
            'username' => $user->name,
            'email' => $user->email,
            'password' => $this->hasher->make(),
            'name_first' => $user->email,
            'name_last' => $user->id,
            'root_admin' => false,
            'cr_slots' => 1,
            'cr_cpu' => 150,
            'cr_ram' => 1536,
            'cr_storage' => 5120,
        ];

        if ($user->email) // Need to check whether the email is in use

        User::forceCreate($data);

        return new JsonResponse([
            'data' => [
                'complete' => true,
                'intended' => $this->redirectPath(),
            ],
        ]);
    }
}