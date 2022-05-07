<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Pterodactyl\Models\Notification;
use Illuminate\Contracts\Hashing\Hasher;
use Pterodactyl\Notifications\AccountCreated;
use Pterodactyl\Http\Requests\Auth\RegisterRequest;
use Illuminate\Contracts\View\Factory as ViewFactory;

class RegisterController extends AbstractLoginController
{
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $view;

    /**
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    private $hasher;

    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * LoginController constructor.
     *
     * @param \Illuminate\Contracts\View\Factory $view
     * @param Hasher $hasher
     */
    public function __construct(
        ViewFactory $view,
        Hasher $hasher
    ) {
        $this->view = $view;
        $this->hasher = $hasher;
    }

    /**
     * Handle all incoming requests for the authentication routes and render the
     * base authentication view component. Vuejs will take over at this point and
     * turn the login area into a SPA.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        return $this->view->make('templates/auth.core');
    }

    /**
     * Handle a register request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|void
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = [
            'uuid' => Uuid::uuid4()->toString(),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'ip_address' => $request->getClientIp(),
            'password' => $this->hasher->make($request->input('password')),
            'name_first' => $request->input('name_first'),
            'name_last' => $request->input('name_last'),
            'cr_slots' => 1,
            'cr_cpu' => 150,
            'cr_ram' => 1536,
            'cr_storage' => 5120,
        ];

        $user = User::forceCreate($data);

        $ip = User::where('ip_address',  $request->getClientIp())->count();

        if ($ip > 1) {
            // Attempt to delete the servers + user when alting is detected.
            // If it doesn't work, no big deal. Their server(s) will eventually
            // get deleted by the renewal system.
            DB::table('servers')->where('owner_id', $user->id)->delete();
            $user->delete();

            return redirect('/auth/error');
        }

        Notification::create([
            'user_id' => $user->id,
            'action' => Notification::ACCOUNT__CREATE,
            'created' => date('d.m.Y H:i:s'),
        ]);

        return new JsonResponse([
            'data' => [
                'complete' => true,
                'intended' => $this->redirectPath(),
            ],
        ]);
    }
}
