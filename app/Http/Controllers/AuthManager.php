<?php

/**
 * Registeration and login class
 */

namespace App\Http\Controllers;

use App\Mail\MailResetPasswordRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Response;

class AuthManager extends Controller {
	function register(Request $request, Response $response) {
		$incomingFields = $request->validate([
			'firstName' => ['required'],
			'LastName' => ['required'],
			'username' => ['required', 'min:3', 'max:20', Rule::unique('users', 'username')],
			'email' => ['required', 'email', Rule::unique('users', 'email')],
			'password' => ['required', 'min:8', 'max:20', 'confirmed'],

		]);
		$incomingFields['password'] = bcrypt($incomingFields['password']);

		$user = new User();
		$user->username = $request->username;
		$user->firstName = $request->firstName;
		$user->LastName = $request->LastName;
		$user->email = $request->email;
		$user->password = bcrypt($request->password);
		$user->role = 'user';

		if ($user->save()) {
			auth()->login($user);
			return response()->json([
				'message' => 'Registration successful',
			], 200);

		} else {
			return response()->json([
				'message' => 'Some error occured, please try again',
			], 500);
		}
	}

	//login
	function login(Request $request, Response $response) {
		$credentials = $request->validate([
			'email' => 'required|email',
			'password' => 'required',
		]);

		if (!auth()->attempt($credentials)) {

			// Authentication failed
			return response()->json([
				'message' => 'Invalid credentials',
			], 401);
		}

		$user = $request->user();

		$user->tokens()->delete();

		if ($user->role == 'admin') {
			$token = $user->createToken('Personal Access Token', ['admin']);
		} else {
			$token = $user->createToken('Personal Access Token', ['user']);
		}

		return response()->json([
			'user' => $user,
			'access_token' => $token->plainTextToken,
			'token_type' => 'Bearer',
			'abilities' => $token->accessToken->abilities,
		], 200, );

	}

	public function resetPasswordRequest(Request $request) {
		$request->validate([
			'email' => 'required|string|email',
		]);

		$user = User::where('email', $request->email)->first();

		if (!$user) {
			return response()->json([
				'message' => 'The provided email is not registered in our system.',
			], 404);
		}

		$code = rand(111111, 999999);
		$user->verification_code = $code;

		if ($user->save()) {
			//todo send the email
			$emailData = array(
				'heading' => 'Reset Password Request',
				'username' => $user->username,
				'email' => $user->email,
				'code' => $user->verification_code,
			);

			Mail::to($emailData['email'])->queue(new MailResetPasswordRequest($emailData));

			return response()->json([
				'message' => 'we have sent a verification code to your email',
			], 200);
		} else {
			return response()->json([
				'message' => 'Sorry, an error occurred, please try again',
			], 500);
		}
	}

	public function resetPassword(Request $request) {
		$request->validate([
			'email' => 'required|string|email',
			'verification_code' => 'required|integer',
			'new_password' => [
				'required',
				'confirmed',
				Password::min(8)
					->letters()
					->mixedCase()
					->numbers()
					->symbols()
					->uncompromised(),
			],
		]);

		$user = User::where('email', $request->email)->where('verification_code', $request->verification_code)->first();

		if (!$user) {
			return response()->json([
				'message' => 'user not found/invalid code',
			], 404);
		}

		$user->password = bcrypt($request->new_password);
		$user->verification_code = NULL;

		if ($user->save()) {
			return response()->json([
				'message' => 'Password updated successfully',
			], 200);
		} else {
			return response()->json([
				'message' => 'some error occurred, please try again later',
			], 500);
		}
	}

	//logout
	public function logout(Request $request): RedirectResponse {
		/*Auth::guard('web')->logout();

			$request->session()->invalidate();

			$request->session()->regenerateToken();

			return redirect('Homefeed');
		*/

		if ($request->user()->tokens()->delete()) {
			return response()->json([
				'message' => 'Logout successful',
			], 200);
		} else {
			return response()->json([
				'message' => 'Some error occurred, please try again',
			], 500);
		}
	}

	public function __invoke(EmailVerificationRequest $request): RedirectResponse {
		if ($request->user()->hasVerifiedEmail()) {
			return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
		}

		if ($request->user()->markEmailAsVerified()) {
			event(new Verified($request->user()));
		}

		return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
	}

}

/*
function login(Request $request, Response $response) {
$incomingFields = $request->validate([
'email' => 'required',
'password' => 'required',
]);
if (auth()->attempt(['email' => $incomingFields['email'], 'password' => $incomingFields['password']])) {
return response()->json($response);

}

}
 */