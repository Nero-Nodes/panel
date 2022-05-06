<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Pterodactyl\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container;
use Pterodactyl\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Services\Users\UserCreationService;
use Pterodactyl\Exceptions\Model\DataValidationException;
use Illuminate\Contracts\Container\BindingResolutionException;

class DiscordController extends Controller
{
    protected AuthManager $auth;
    protected UserCreationService $creationService;

    /**
     * @throws BindingResolutionException
     */
    public function __construct(UserCreationService $creationService)
    {
        $this->auth = Container::getInstance()->make(AuthManager::class);
        $this->creationService = $creationService;
    }

    /**
     * Uses the Discord API to return a user objext.
     * 
     * @return JsonResponse
     */
    public function oauth(): JsonResponse
    {
        return new JsonResponse([
            'https://discord.com/api/oauth2/authorize?client_id='
            .config('discord.client_id').'&redirect_uri='
            .urlencode(config('discord.redirect_url'))
            .'&response_type=code&scope=identify%20email%20guilds%20guilds.join&prompt=none'
        ], 200, [], null, false);
    }

    /**
     * Returns data from the Discord API to login.
     * 
     * @throws DisplayException
     * @throws DataValidationException
     */
    public function authenticate(Request $request)
    {
        $code = Http::asForm()->post('https://discord.com/api/oauth2/token', [
            'client_id' => config('discord.client_id'),
            'client_secret' => config('discord.client_secret'),
            'grant_type' => 'authorization_code',
            'code' => $request->input('code'),
            'redirect_uri' => config('discord.redirect_url'),
        ]);

        if (!$code->ok()) return;
        $req = json_decode($code->body());
        if (preg_match("(email|guilds|identify|guilds.join)", $req->scope) !== 1) return;

        $user_info = json_decode(Http::withHeaders(["Authorization" => "Bearer ".$req->access_token])->asForm()->get('https://discord.com/api/users/@me')->body());
        $banned = Http::withHeaders(["Authorization" => "Bot ".config('bot_token')])->get('https://discord.com/api/guilds/'.config('discord.guild_id').'/bans/'.$user_info->id);

        if ($banned->ok()) {
            return redirect('/auth/error');
        }

        if (User::where('email', $user_info->email)->exists()) {
            $user = User::query()->where('email', $user_info->email)->first();
            DB::table('users')->where('email', $user_info->email)->update(['ip_address' => $request->getClientIp()]);
            Auth::loginUsingId($user->id, true);
            return redirect('/');
        } else {
            $username = $this->genString(8);
            $new_user = [
                'email' => $user_info->email,
                'username' => $username,
                'name_first' => $user_info->username,
                'name_last' => $user_info->discriminator,
                'ip_address' => $request->getClientIp(),
                'password' => $this->genString(128),
                'cr_slots' => 1,
                'cr_cpu' => 150,
                'cr_ram' => 1536,
                'cr_storage' => 5120,
            ];

            try {
                $this->creationService->handle($new_user);
            } catch (Exception $e) { /* do nothing */ }

            $user = User::where('username', $username)->first();
            $ip = User::where('ip_address',  $request->getClientIp())->count();

            if ($ip > 1) {
                $user->delete();
                return redirect('/auth/error');
            }

            Auth::loginUsingId($user->id, true);

            Notification::create([
                'user_id' => $user->id,
                'action' => Notification::ACCOUNT__CREATE,
                'created' => date('d.m.Y H:i:s'),
            ]);

            return redirect('/');
        }
    }

    /**
     * Returns a string used for creating a users
     * username and password on the Panel.
     */
    public function genString(int $length): string
    {
        $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        return substr(str_shuffle($chars), 0, $length);
    }
}
