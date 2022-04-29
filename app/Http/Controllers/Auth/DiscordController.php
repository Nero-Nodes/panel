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
     * Uses the Discord API to login a user objext.
     * 
     * @return JsonResponse
     */
    public function authorizeLogin(): JsonResponse
    {
        return new JsonResponse(['https://discord.com/api/oauth2/authorize?client_id='.config('discord.client_id').'&redirect_uri='.urlencode(config('discord.redirect_url_login')).'&response_type=code&scope=identify%20email%20guilds%20guilds.join'], 200, [], null, false);
    }

    /**
     * Uses the Discord API to return a user objext.
     * 
     * @return JsonResponse
     */
    public function authorizeRegister(): JsonResponse
    {
        return new JsonResponse(['https://discord.com/api/oauth2/authorize?client_id='.config('discord.client_id').'&redirect_uri='.urlencode(config('discord.redirect_url_register')).'&response_type=code&scope=identify%20email%20guilds%20guilds.join'], 200, [], null, false);
    }

    /**
     * Returns data from the Discord API to login.
     * This endpoint should only be hit when a user
     * has already signed up to the Portal.
     * 
     * @throws DisplayException
     * @throws DataValidationException
     */
    public function login(Request $request): void
    {
        $code = Http::asForm()->post('https://discord.com/api/oauth2/token', [
            'client_id' => config('discord.client_id'),
            'client_secret' => config('discord.client_secret'),
            'grant_type' => 'authorization_code',
            'code' => $request->input('code'),
            'redirect_uri' => config('discord.redirect_url_login'),
        ]);

        if (!$code->ok()) throw new DisplayException('Unable to authenticate: Invalid response code.');
        $req = json_decode($code->body());

        if (preg_match("(email|guilds|identify|guilds.join)", $req->scope) !== 1) {
            throw new DisplayException('Unable to authenticate: Login scopes incorrect.');
        }

        $user_info = json_decode(Http::withHeaders(["Authorization" => "Bearer ".$req->access_token])->asForm()->get('https://discord.com/api/users/@me')->body());
        $banned = Http::withHeaders(["Authorization" => "Bot ".config('bot_token')])->get('https://discord.com/api/guilds/'.config('discord.guild_id').'/bans/'.$user_info->id);

        if ($banned->ok()) {
            throw new DisplayException('Unable to authenticate: This account has been deactivated by Nero. Please contact us for support at https://neronodes.net/discord.');
        }

        try {
            $user = User::where('discord_id', $user_info->id)->get();
        } catch (DisplayException $e) {
            throw new DisplayException('Unable to authenticate: User does not exist. Please register first.');
        }

        Auth::loginUsingId($user->id, true);
    }

    /** 
     * Registers a user with Discord Oauth.
     * Endpoint should only be used when registering 
     * a user, not logging in.
     * 
     * @throws DisplayException
     * @throws DataValidationException
     */
    public function register(Request $request): void
    {
        $code = Http::asForm()->post('https://discord.com/api/oauth2/token', [
            'client_id' => config('discord.client_id'),
            'client_secret' => config('discord.client_secret'),
            'grant_type' => 'authorization_code',
            'code' => $request->input('code'),
            'redirect_uri' => config('discord.redirect_url_register'),
        ]);

        if (!$code->ok()) throw new DisplayException('Unable to authenticate: Invalid response code.');
        $req = json_decode($code->body());

        if (preg_match("(email|guilds|identify|guilds.join)", $req->scope) !== 1) {
            throw new DisplayException('Unable to authenticate: Login scopes incorrect.');
        }

        $user_info = json_decode(Http::withHeaders(["Authorization" => "Bearer ".$req->access_token])->asForm()->get('https://discord.com/api/users/@me')->body());
        $banned = Http::withHeaders(["Authorization" => "Bot ".config('bot_token')])->get('https://discord.com/api/guilds/'.config('discord.guild_id').'/bans/'.$user_info->id);

        if ($banned->ok()) {
            throw new DisplayException('Unable to authenticate: This account has been deactivated by Nero. Please contact us for support at https://neronodes.net/discord.');
        }

        $username = $this->genString(8);

        $new_user = [
            'email' => $user_info->email,
            'username' => $username,
            'name_first' => $user_info->username,
            'name_last' => $user_info->discriminator,
            'password' => $this->genString(256), // Unnecessarily long, just seems more secure.
        ];

        try {
            $this->creationService->handle($new_user);
        } catch (DisplayException $ex) {
            throw new DisplayException('Your account could not be created. Try signing in first.');
        }

        $user = User::query()->where($this->getField($username), $username)->first();

        dd($user);
        // Auth::loginUsingId($request->user()->id, true);
    }

    public function genString(int $length): string
    {
        $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        return substr(str_shuffle($chars), 0, $length);
    }
}
