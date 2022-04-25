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
     * @return JsonResponse
     */
    public function login(): JsonResponse
    {
        return new JsonResponse(['https://discord.com/api/oauth2/authorize?client_id='.config('discord.client_id').'&redirect_uri='.urlencode(config('discord.redirect_url')).'&response_type=code&scope=identify%20email%20guilds%20guilds.join'], 200, [], null, true);
    }

    /**
     * @param Request $request
     * @return void
     * @throws DisplayException
     * @throws DataValidationException
     */
    public function callback(Request $request): void
    {
        $code = Http::asForm()->post('https://discord.com/api/oauth2/token', [
            'client_id' => config('discord.client_id'),
            'client_secret' => config('discord.client_secret'),
            'grant_type' => 'authorization_code',
            'code' => $request->input('code'),
            'redirect_uri' => config('redirect_url')
        ]);
        if (!$code->ok()) throw new DisplayException('Invalid Return Code');
        $req = json_decode($code->body());
        if (preg_match("(email|guilds|identify|guilds.join)", $req->scope) !== 1) throw new DisplayException('Invalid Authorized Scopes');
        $user_info = json_decode(Http::withHeaders(["Authorization" => "Bearer ".$req->access_token])->asForm()->get('https://discord.com/api/users/@me')->body());
        $banned = Http::withHeaders(["Authorization" => "Bot ".config('bot_token')])->get('https://discord.com/api/guilds/'.config('discord.guild_id').'/bans/'.$user_info->id);
        if ($banned->ok()) throw new DisplayException('This account has been deactivated by Nero. Please contact us for support at https://neronodes.net/discord.');
        Http::withHeaders(["Authorization" => "Bot ".config('bot_token')])->put('https://discord.com/api/guilds/'.config('discord.guild_id').'/members/'.$user_info->id, ["access_token" => $req->access_token]);
        $user = User::query()->where('discord_id', '=', $user_info->id)->first()->get()[0];
        if (!isset($user)) {
            $new_user = [
                'email' => $user_info->email,
                'username' => $this->genString(8),
                'first_name' => $user_info->username,
                'last_name' => $user_info->discriminator,
                'password' => $this->genString(32)
            ];
            $this->creationService->handle($new_user);
            $user = User::query()->where('discord_id', '=', $user_info->id)->first()->get()[0];
        };
        Auth::loginUsingId($user->getAttribute('id'), true);
    }

    public function genString(int $length): string
    {
        $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        return substr(str_shuffle($chars), 0, $length);
    }
}
