<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Mail\TestMail;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function changePassword(ChangePasswordRequest $request) 
    {
        $password = sha1($request->password);
        $user_id = $request->user_id;

        try{
            User::updatePassword($password, $user_id);
        }
        catch(QueryException $e) {
            Log::critical($e->getMessage());
            return response('Bad request', 400);
        }
        catch(\Exception $e) {
            Log::debug($e->getMessage());
            return response(null, 500);
        }        
    }

    public function forgotPassword(ForgotPasswordRequest $request) 
    {
        $email = $request->email;
        
        try{
            DB::beginTransaction();

            $password = Str::password(12,true,true,false,false);

            User::changeForgotPassword($email, $password);

            Mail::to($request->email)->send(new TestMail([
                'title' => 'Registration',
                'body' => "Your password is: $password"
            ]));
        
            DB::commit();

            return response()->json('Email with new password has been sent', 204);
        }
        catch(QueryException $e) {
            DB::rollBack();
            Log::critical($e->getMessage());
            return response(null, 400);
        }
        catch(\Exception $e) {
            DB::rollBack();
            Log::debug($e->getMessage());
            return response(null, 500);
        }        
    }
}
