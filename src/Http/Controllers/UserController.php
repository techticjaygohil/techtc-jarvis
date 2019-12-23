<?php
namespace App\Api\Controllers;

use App\Api\Requests\Auth\LoginRequest;
use App\Api\Requests\Auth\RegisterRequest;
use App\Api\Requests\Auth\ForgetPasswordRequest;
use App\Api\Requests\Auth\UpdateRegisterRequest;
use App\Api\Requests\Auth\ChangePasswordRequest;
use App\Api\Requests\SetPasswordRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ForgetPasswordNotification;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
* @resource Auth
*/

class UserController extends Controller {
    /**
    * Login
    */

    public function authenticate( LoginRequest $request ) {
        $credentials = $request->only( 'email', 'password' );

        try {

            //attempt login for token
            if ( !$token = JWTAuth::attempt( $credentials ) ) {
                return response()->error( 'Incorrect email address or password' );
            }
            $user = Auth::user();
            //check user with role
            $userRole = $user->roles()->first()->name;

            if ( $userRole == 'Admin' ) {
                //this condition is for PRO app because, it used for all other type of users except Customer
            } else if ( $userRole != 'Customer' ) {
                return response()->error( 'user type not match' );
            }

            //update last login information
            $user->last_login_at = Carbon::now();

            //update timezone
            if ( !empty( $request->timezone ) ) {
                $user->timezone = $request->timezone;
            }

            //save and load additional details for return
            $user->last_login_at = date( 'Y-m-d H:i:s' );
            $user->save();

            if ( !empty( $request->device_type ) && !empty( $request->device_token ) ) {
                $device_token['device_token']  = $request->device_token;
                $device_token['device_type'] = $request->device_type;
                $device_token['user_id'] = $user->id;
                DeviceToken::updateOrCreate( $device_token );
            }

            return response()->success( [
                'data' => $user,
                'token' => $token,
                'status_code' =>  200,
            ] );

        } catch ( JWTException $e ) {
            return response()->error( 'Could not create token' );
        }
    }

    /**
    * Register
    */

    public function register( RegisterRequest $request ) {
        $data = $request->only( ['name', 'email', 'password', 'phone'] );
        $data['password']   = Hash::make( $data['password'] );

        /*$userName = explode( '@', $data['email'] );
        $data['name'] = ucwords( str_replace( '.', ' ', $userName[0] ) );
        */

        if ( !isset( $request->type ) || empty( $request->type ) ) {
            $request->type = 'Customer';
        }
        $role_id = Role::where( 'name', $request->type )->first();
        if ( empty( $role_id ) ) {
            return response()->error( 'Invalid type' );
        }
        $user  = User::create( $data );

        if ( $user ) {
            $user->roles()->sync( $role_id );
            if ( !empty( $request->device_type ) && !empty( $request->device_token ) ) {
                $device_token['device_token']  = $request->device_token;
                $device_token['device_type'] = $request->device_type;
                $user->deviceToken()->create( $device_token );
            }
            $token = JWTAuth::fromUser( $user );
            $user->load( ['roles'] );

        }

        return response()->json( [
            'status_code' =>  200,
            'data' => $user,
            'token' => $token
        ], 200 );

    }

    /**
    * Update Profile
    */

    public function updateProfile( UpdateRegisterRequest $request ) {
        $user = User::find( Auth::id() );
        if ( $user ) {
            $fields = ['name', 'email', 'phone', 'profile_pic'];
            foreach ( $fields as $field ) {
                if ( $request->exists( $field ) ) {
                    $user->$field = $request->$field;
                }
            }
            $user->save();

            return response()->success( [
                'status_code' => 200,
                'message'     => 'Your profile successfully updated.',
                'data' => $user
            ] );
        }
    }

    /**
    * Forget Password
    */

    public function forgetPassword( ForgetPasswordRequest $request ) {

        $user = User::where( 'email', $request->get( 'email' ) )->first();
        if ( !$user ) {
            return response()->json( [
                'status_code' => 400,
                'message' => 'Entered email address not found.'
            ], 400 );
        } else {
            if ( isset( $user->password ) && $user->password == '' ) {
                return response()->json( [
                    'status_code' => 400,
                    'message' => 'you are login with social media.'
                ], 400 );
            }
            $user->update( ['remember_token' => str_random( 10 )] );
            $user['password'] = str_random( 8 );
            $hash_password    = Hash::make( $user['password'] );
            $user->notify( new ForgetPasswordNotification( $user ) );
            $password_update = User::where( 'id', $user->id )->update( ['password' => $hash_password] );
            return response()->json( [
                'status_code' => 200,
                'message'     => 'Please check your email address to reset password.',
            ] );
        }
    }

    /**
    * Change Password
    */

    public function changePassword( ChangePasswordRequest $request ) {
        $user = User::find( Auth::id() );
        if ( $user ) {
            if ( Hash::check( $request->old_password, $user->password ) ) {
                $user->password = Hash::make( $request['new_password'] );
                $user->save();
                return response()->json( ['status_code' => 200, 'message' => 'Password has been updated.'], 200 );
            } else {
                return response()->json( ['status_code' => 400, 'message' => 'Entered old password is incorrect.'], 400 );
            }
        } else {
            return response()->json( ['status_code' => 400, 'message' => 'User not found'], 400 );
        }
    }

    /**
    * Get Current User
    */

    public function getUser() {
        $user = User::find( Auth::id() );

        return response()->json( [
            'status_code'  => 200,
            'data'         => $user->load( ['subscriptions', 'notification'] )
        ], 200 );
    }

    /**
    * Logout
    */

    public function logout( Request $request ) {
        try {
            Auth::logout();

            return response()->json( [
                'status_code' => 200,
                'message' => 'User logged out successfully'
            ] );
        } catch ( JWTException $exception ) {
            return response()->json( [
                'status_code' => 400,
                'message' => 'Sorry, the user cannot be logged out'
            ], 500 );
        }
    }

}
