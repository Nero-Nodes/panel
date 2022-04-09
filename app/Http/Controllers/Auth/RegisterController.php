<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Pterodactyl\Http\Requests\Auth\RegisterRequest;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Pterodactyl\Models\User;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Hashing\Hasher;
use Pterodactyl\Notifications\AccountCreated;

class RegisterController extends AbstractLoginController
{
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $view;

    /**
     * @var \Illuminate\Contracts\Auth\PasswordBroker
     */
    private $passwordBroker;

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
     * @param PasswordBroker $passwordBroker
     * @param Hasher $hasher
     */
    public function __construct(
        ViewFactory $view,
        PasswordBroker $passwordBroker,
        Hasher $hasher
    ) {
        $this->view = $view;
        $this->passwordBroker = $passwordBroker;
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
            // While this seems super insecure, it isn't. The hasher
            // immediately executes in the request so the database
            // doesn't even see the unhashed password once. Neat.
            'password' => $this->hasher->make($request->input('password')),
            'name_first' => $request->input('name_first'),
            'name_last' => $request->input('name_last'),
            'root_admin' => false,
            'cr_slots' => 1,
            'cr_cpu' => 150,
            'cr_ram' => 1536,
            'cr_storage' => 5120,
        ];

        $user = User::forceCreate($data);
        $token = $this->passwordBroker->createToken($user);

        return new JsonResponse([
            'data' => [
                'complete' => true,
                'intended' => $this->redirectPath(),
            ],
        ]);
    }
}
