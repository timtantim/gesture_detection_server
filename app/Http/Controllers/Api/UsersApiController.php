<?php

namespace App\Http\Controllers\Api;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Auth;
use DB;
use Logger;
use Facade\FlareClient\Http\Response;

class UsersApiController extends Controller
{
    /**
     * Attempt to Create auth token
     *
     * @return token
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register_account(Request $request)
    {
        /**
         * Get a validator for an incoming registration request.
         *
         * @param  array  $request
         * @return \Illuminate\Contracts\Validation\Validator
         */
        $valid = validator($request->only('email', 'name', 'password', 'mobile', 'user_account'), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'user_account'=>'required|string|max:255'
        ]);

        if ($valid->fails()) {
            $jsonError = response()->json($valid->errors()->all(), 400);
            return \Response::json($jsonError);
        }

        $data = request()->only('email', 'name', 'password','user_account');

        $user = User::create([
            'name' => $data['name'],
            'user_account'=>$data['user_account'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);
        event(new Registered($user));
        // And created user until here.

        // $client = Client::where('password_client', 1)->first();
        $client = DB::table('oauth_clients')->where('password_client',1)->first();

        // Is this $request the same request? I mean Request $request? Then wouldn't it mess the other $request stuff? Also how did you pass it on the $request in $proxy? Wouldn't Request::create() just create a new thing?

        $request->request->add([
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $data['email'],
            'password' => $data['password'],
            'scope' => null,
        ]);

        // Fire off the internal request.
        $token = Request::create(
            'oauth/token',
            'POST'
        );

        return \Route::dispatch($token);
    }

     /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authentication(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($credentials)) {
            
            $client = DB::table('oauth_clients')->where('password_client',1)->first();
            $request->request->add([
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'username' => $request->email,
                'password' => $request->password,
                'scope' => null,
            ]);
            $token = Request::create(
                'oauth/token',
                'POST'
            );
            $response=json_decode( \Route::dispatch($token)->getOriginalContent());
            $user_account=Auth::user()->user_account;
            $response->user_account=$user_account;
            Logger::db_log($user_account,__FILE__,__LINE__,$request->all(),200,$response,'??????');
            Logger::info("?????? $user_account ??????",__FILE__,__LINE__);
            // dd($token);
            return response(json_encode( $response),200);
            //
            // return \Route::dispatch($token);
        }else{
            return response(['message'=>'??????????????????'],500);
        }
 
        
    }

    /**
     * Attempt to refresh auth token
     *
     * @return token
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function refresh_oauth_token(Request $request)
    {
        $refresh_token = $request->header('Refreshtoken');
        $client = DB::table('oauth_clients')->where('password_client',1)->first();
        $request->request->add([
            'grant_type' => 'refresh_token',
            'refresh_token'=>$refresh_token,
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => null,
        ]);
        // Fire off the internal request.
        $token = Request::create(
            'oauth/token',
            'POST'
        );
        // $response=json_decode( \Route::dispatch($token)->getOriginalContent());
        // $response->user_account=Auth::user()->user_account;
        return \Route::dispatch($token);
        // return response(json_encode( $response),200);
    }

    /**
     * Revoke auth token
     *
     * @return token
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function revoke_token(Request $request)
    {
        $request->user()->token()->revoke();
        $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($request->user()->token()->id);

        return response()->json('Logged out successfully', 200);
    }

    
}
