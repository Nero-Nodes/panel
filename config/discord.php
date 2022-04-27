<?php

return [
    'client_id' => env('DISCORD_CLIENT_ID', false),
    'client_secret' => env('DISCORD_CLIENT_SECRET', false),
    'bot_token' => env('DISCORD_BOT_TOKEN', false),
    'redirect_url_login' => env('DISCORD_REDIRECT_URL_LOGIN', false),
    'redirect_url_register' => env('DISCORD_REDIRECT_URL_REGISTER', false),
    'guild_id' => env('DISCORD_GUILD_ID', false)
];
