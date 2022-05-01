<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Pterodactyl\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Container\Container;
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
    public function authorize(): JsonResponse
    {
        return new JsonResponse([
            'https://discord.com/api/oauth2/authorize?client_id='
            .config('discord.client_id').'&redirect_uri='
            .urlencode(config('discord.redirect_url'))
            .'&response_type=code&scope=identify%20email%20guilds%20guilds.join'
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
            'redirect_uri' => config('discord.redirect_url_login'),
        ]);

        if (!$code->ok()) return;
        if (preg_match("(email|guilds|identify|guilds.join)", $req->scope) !== 1) return;

        $req = json_decode($code->body());
        $user_info = json_decode(Http::withHeaders(["Authorization" => "Bearer ".$req->access_token])->asForm()->get('https://discord.com/api/users/@me')->body());
        $banned = Http::withHeaders(["Authorization" => "Bot ".config('bot_token')])->get('https://discord.com/api/guilds/'.config('discord.guild_id').'/bans/'.$user_info->id);

        if ($banned->ok()) {
            redirect('/auth/login');
            return throw new DisplayException('Unable to authenticate: This account has been deactivated by Nero. Please contact us for support at https://neronodes.net/discord.');
        }

        if (User::where('email', $user_info->email)->exists()) {
            $user = User::query()->where('email', $user_info->email)->first();
            return Auth::loginUsingId($user->id, true);
        } else {
            $username = $this->genString(8);
            $new_user = [
                'email' => $user_info->email,
                'username' => $username,
                'name_first' => $user_info->username,
                'name_last' => $user_info->discriminator,
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
            return Auth::loginUsingId($user->id, true);
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
