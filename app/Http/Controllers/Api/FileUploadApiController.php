<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Files;
use App\Traits\ResponseAPI;
use Auth;
use Validator;
use Illuminate\Http\Request;
use App\Events\HelpAlertEvent;
use Storage;
use Image;
use DB;
/**
 * @File Management
 *
 * APIs for managing files
 */
class FileUploadApiController extends Controller
{
    use ResponseAPI;


    /**
     * show_user_file
     *
     * 讀取用戶檔案.
     *
     * @group File Management
     * @authenticated
     * @response 400 scenario="Service is unhealthy" {"status": "down", "services": {"database": "up", "redis": "down"}}
     * @responseField Base64 檔案
     */
    public function show_user_file($user_account,$slug)
    {
        // $slug=$request->route()->parameters()["slug"];
        // $user_account=$request->route()->parameters()["user_account"];
        //dd($user_account);
        //$slug,$user_account
        // dd($request->route()->parameters()["slug"]);
        // $validator = Validator::make($request->route()->parameters(), [
        //     // 'file' => 'required|mimes:doc,docx,pdf,txt,csv|max:2048',
        //     'slug' => 'required',
        //     'user_account'=>'required'
        // ]);
      
        // if ($validator->fails()) {
        //     return $this->error($validator->errors(), 401);
        // }
        // $storagePath = public_path('storage/files/' . Auth::user()->id . '/' . $slug);
        $storagePath = public_path('storage/files/' . $user_account . '/' . $slug);
        // dd($storagePath);
        $b64image = base64_encode(file_get_contents($storagePath));
        return response($b64image, 200);
    }
    /**
     * upload_file
     *
     * 上傳檔案.
     * @group File Management
     * @authenticated
     * @response 400 scenario="Service is unhealthy" {"status": "down", "services": {"database": "up", "redis": "down"}}
     * @responseField message 提示訊息   
     * @responseField error bool 錯誤狀態 
     * @responseField code int HTTP Code  
     * @responseField results json 回傳結果 
     */
    public function upload_file(Request $request)
    {
        $user_account=$request->user_account;
        // $validator = Validator::make($request->all(), [
        //     // 'file' => 'required|mimes:doc,docx,pdf,txt,csv|max:2048',
        //     'file' => 'required|mimes:png,jpeg,jpg|max:2048',
        //     'user_account'=>'required'
        // ]);

        // if ($validator->fails()) {
        //     return $this->error($validator->errors(), 401);
        // }
        $validator = Validator::make($request->all(), [
            'user_account'=>'required'
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors(), 401);
        }

        try {
            if ($file = $request->file('file')) {
                //如果傳入File 的處理方式
                $validator = Validator::make($request->all(), [
                    'file' => 'required|mimes:png,jpeg,jpg|max:2048',
                ]);
        
                if ($validator->fails()) {
                    return $this->error($validator->errors(), 401);
                }

                // $path = $file->store('public/files/'.Auth::user()->id);
                $path = $file->store('public/files/'.$user_account);
                $name = $file->getClientOriginalName();
                //basename($path)//取得加密後的檔名
                //store your file into directory and db
                // dd($name);
                $file = new Files();
                $file->user_account=$user_account;
                $file->name = $name;
                $file->path = Storage::url($path);
                $file->save();
               
                return $this->success("File successfully uploaded", $file);
            }else{
                //如果傳入Base64 的處理方式
                $validator = Validator::make($request->all(), [
                    'base64file' => 'required',
                ]);
        
                if ($validator->fails()) {
                    return $this->error($validator->errors(), 401);
                }
                $folderPath = 'public/files/'.$user_account.'/';
                $name=uniqid() . '.png';
                $file = $folderPath . $name;
                $path=Storage::put($file, base64_decode($request->base64file));
                $file = new Files();
                $file->user_account=$user_account;
                $file->name = $name;
                $file->path = "/storage/files/{$user_account}/{$name}";
                $file->save();
                // dd('123');
                event(new HelpAlertEvent($user_account,$user_account));
            }

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

    }

     /**
     * upload_multiple_file
     *
     * 上傳檔案.
     * @group File Management
     * @authenticated
     * @response 400 scenario="Service is unhealthy" {"status": "down", "services": {"database": "up", "redis": "down"}}
     * @responseField message 提示訊息   
     * @responseField error bool 錯誤狀態 
     * @responseField code int HTTP Code  
     * @responseField results json 回傳結果 
     */
    public function upload_multiple_file(Request $request)
    {
        if(!$request->hasFile('files')) {
            return $this->error('upload_file_not_found', 400);
        }
        $user_account=$request->user_account;
        $validator = Validator::make($request->all(), [
            'user_account'=>'required'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 401);
        }
        try {
            $allowedfileExtension=['pdf','jpg','png','doc','docx','pdf','txt','csv'];

            foreach($request->file('files') as $file)
            {
                $extension = $file->getClientOriginalExtension();
                $check = in_array($extension,$allowedfileExtension);
                if($check) {
                    //basename($path)//取得加密後的檔名
                    $path = $file->store('public/files/'.$user_account);
                    $name = $file->getClientOriginalName();
    
                    $save = new Files();
                    $save->name = $name;
                    $save->path = Storage::url($path);
                    $save->save();
                }
            }
            return $this->success("File successfully uploaded", $file);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

    }


    /**
     * load_all_files
     *
     * 取得所有偵測圖檔.
     * @group File Management
     * @authenticated
     * @response 400 scenario="Service is unhealthy" {"status": "down", "services": {"database": "up", "redis": "down"}}
     * @responseField message 提示訊息   
     * @responseField error bool 錯誤狀態 
     * @responseField code int HTTP Code  
     * @responseField results json 回傳結果 
     */
    public function load_all_files(Request $request)
    {
        
        try {
            // $files = Files::all();
            $files =Files::orderBy('created_at','desc')->where('user_account',Auth::user()->user_account)->orderBy('id', 'desc')->take(10)->get();
            // $files = DB::table('files')->get();
            // dd(DB::table('files')->get());
            return $this->success("Get All Files", $files);
        } catch (\Exception $e) {
            // return $this->error($e->getMessage(), $e->getCode());
            return response($e->getMessage(),500);
        }

    }
}
