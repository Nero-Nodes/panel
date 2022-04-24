<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Exception;
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
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * @return JsonResponse
     */
    public function login(): JsonResponse
    {
        return new JsonResponse(['https://discord.com/api/oauth2/authorize?client_id='.env('DISCORD_CLIENT_ID').'&redirect_uri='.urlencode(env('REDIRECT_URL')).'&response_type=code&scope=identify%20email%20guilds%20guilds.join'], 200, [], null, true);
    }

    /**
     * @param Request $request
     * @return void
     * @throws DisplayException
     */
    public function callback(Request $request)
    {
        $code = Http::asForm()->post('https://discord.com/api/oauth2/token', ['client_id' => env('DISCORD_CLIENT_ID'), 'client_secret' => env('DISCORD_CLIENT_SECRET'), 'grant_type' => 'authorization_code', 'code' => $request->input('code'), 'redirect_uri' => env('REDIRECT_URL')]);
        if (!$code->ok()) throw new DisplayException('Invalid Return Code');
        $req = json_decode($code->body());
        if (preg_match("(email|guilds|identify|guilds.join)", $req->scope) !== 1) throw new DisplayException('wrong scopes boi');
        $user_info = json_decode(Http::withHeaders(["Authorization" => "Bearer ".$req->access_token])->asForm()->get('https://discord.com/api/users/@me')->body());
        $banned = Http::withHeaders(["Authorization" => "Bot ".env('DISCORD_BOT_TOKEN')])->get('https://discord.com/api/guilds/957896904467968061/bans/'.$user_info->id);
        if ($banned->ok()) throw new DisplayException('You are currently banned from Nero Nodes!');
        Http::withHeaders(["Authorization" => "Bot ".env('DISCORD_BOT_TOKEN')])->put('https://discord.com/api/guilds/957896904467968061/members/'.$user_info->id, ["access_token" => $req->access_token]);
        try {
            $user = User::query()->where('discord_id', '=', $user_info->id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $new_user = [
                'email' => $user_info->email,
                'username' => $this->genString(8),
                'first_name' => $user_info->username,
                'last_name' => $user_info->discriminator,
                'password' => $this->genString(32)
            ];
            $this->creationService->handle($new_user);
            $user = User::query()->where('discord_id', '=', $user_info->id)->firstOrFail();
        }
        Auth::loginUsingId($user->getAttribute('id'), true);
    }

    public function genString(int $length): string
    {
        $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        return substr(str_shuffle($chars), 0, $length);
    }
}
