<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/13
 * Time: 15:36
 */

namespace App\Http\Controllers\Api\V1;
use App\Mail\ReviewLoged;
use App\Models\User;
use App\Models\Verfication;
use Illuminate\Http\Request;
use App\Models\Sms;
use Tymon\JWTAuth\Manager;
use Validator;

//use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;

use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Mail;
class ToolsController extends BaseController
{

    protected $jwtManager;

    public function __construct(Manager $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function upload(Request $request)
    {
        //判断请求中是否包含name=file的上传文件
        if (!$request->hasFile('file')) {
            return $this->responseError('请上传图片');
        }
        // 判断图片上传中是否出错
        $file = $request->file('file');
        if (!$file->isValid()) {
            return $this->responseError('上传图片出错，请重试');
        }
        $credentials = $request->all();
        $entension = $file->getClientOriginalExtension(); //  上传文件后缀
        $filename = date('ymdHis').mt_rand(100,999);  // 重命名图片
        if(!empty($entension)){
            $filename .= '.'.$entension;
        }
        if(!isset($credentials['dir'])){
            $credentials['dir'] = 'temp';
        }
        $host = url('/');
        $file->move(public_path().'/uploads/'.$credentials['dir'].'/',$filename);  // 重命名保存
        $img_path = $host.'/uploads/'.$credentials['dir'].'/'.$filename;
        return array('url'=>$img_path);
    }

    public function upload_base64(Request $request)
    {
        $credentials = $request->all();
        if(!isset($credentials['file'])){
            return $this->responseError('傳輸數據出錯');
        }
        $base64_img = trim($credentials['file']);
        if(!isset($credentials['dir'])){
            $credentials['dir'] = 'temp';
        }

        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $entension = $result[2];//图片后缀
            $filename = uniqid().mt_rand(100,999).'.'.$entension;
            $new_file = public_path().'/upload/'.$credentials['dir'].'/'.$filename;
            $host = url('/');
            if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                $img_path = $host.'/upload/'.$credentials['dir'].'/'.$filename;

                return $this->responseSuccess(array('url'=>$img_path));

            }else{
                return $this->responseError('图片上传失败');
            }

        }else{
            return $this->responseError('文件错误');

        }

    }

    public function uploadvideo(Request $request)
    {
        if(!isset($_FILES['file'])){
            return $this->responseError('请上传音頻');
        }
        $file = $_FILES['file'];

        $tmpName = $file['tmp_name'];
        $name =$file["name"];

        $filename = uniqid().substr($name,strrpos($name,'.'));
        move_uploaded_file($file["tmp_name"],public_path()."/uploads/video/" . $filename);

        return ['url'=>url('uploads/video').'/'.$filename];
    }

    public function sendSms(Request $request){
        $credentials = $request->only(['zone', 'phone']);

        $validator = Validator::make($credentials, [
            'phone' => 'required',
        ]);
        if($validator->fails()) {
            return $this->responseError($validator->errors()->first(),422);
        }
        $phone = trim($credentials['phone']);
        if(isset($credentials['zone']) && trim($credentials['zone'])){
            $zone = trim($credentials['zone']);
        }else{
            $zone = $this->phoneZone;
        }

        //sms 白名单
        $whiteList = config('wakasann.sms_white_list');
        if(is_array($whiteList)){
            if(array_key_exists($phone,$whiteList)){
                return [
                    'code' => mt_rand(1,9)
                ];
            }
        }


        if(strpos($zone,"852") !== false){
            $smsModel = new Sms();
        }else{
            $smsModel = new Sms();
        }

        $smsCode = $smsModel->crateSmsCode();
        //$msg = urlencode("您的驗證碼為 {$smsCode}，請在APP上輸入此驗證碼。");
        $msg = urlencode("[sms]{$smsCode}");

        if(App()->environment() == 'production') {
            $smsModel->sendSms($msg, $phone, $zone);
        }

        \DB::table('verification')->where('verification_account', '=', $phone)->where('zone', '=', $zone)->delete();

        $verification = new Verfication();
        $verification->verification_account = $phone;
        $verification->zone = $zone;
        $verification->verification_code = $smsCode;
        $verification->send_at = time();
        $verification->save();

        if(App()->environment() != 'production'){
            return [
                'code' => $smsCode
            ];
        }else{
            return [];
        }

    }

    public function checkSmsCodeForApi(Request $request)
    {
        $credentials = $request->only(['code', 'phone','zone']);

        $validator = Validator::make($credentials, [
            'phone' => 'required',
            'code'  => 'required',
        ]);
        if($validator->fails()) {
            return $this->responseError($validator->errors()->first(),422);
        }

        $phone = trim($credentials['phone']);
        if(isset($credentials['zone']) && trim($credentials['zone'])){
            $zone = trim($credentials['zone']);
        }else{
            $zone = $this->phoneZone;
        }
        //sms 白名单
        $whiteList = config('wakasann.sms_white_list');
        if(is_array($whiteList)){
            if(array_key_exists($phone,$whiteList)){
                if($credentials['code'] != $whiteList[$phone]){
                    return [ 'success' => false ];
                }else{
                    return [ 'success' => true ];
                }
            }
        }
        $result = $this->checkSmsCode($phone,$zone,$credentials['code']);
        return ['success' => $result];
    }

    public function checkUserName(Request $request)
    {
        $credentials = $request->all();
        $validator = Validator::make($credentials, [
            'username' => 'required',
        ]);
        if($validator->fails()) {
            return $this->responseError($validator->errors()->first(),422);
        }
        $count = User::where('username','=',$credentials['username'])->count();
        return [
            'success' => ($count > 0)?false:true
        ];
    }


    /**
     * 刷新token
     * @param Request $request
     * @return mixed
     */
    public function refreshToken(Request $request){
        try{
            $old_token = JWTAuth::getToken();
            //get jwt token
            $token = $this->jwtManager->refresh($old_token)->get();
        }catch (TokenExpiredException $e){
            return $this->json($e->getMessage(), 500);
        }catch(JWTException $e){
            return $this->json($e->getMessage(), 500);
        }
        return $this->json([
            'token' => $this->jwtManager->refresh($token)->get()
        ]);
    }

    public function tokenCheck(Request $request){
        $result = [
            'success' => false
        ];
        try {
            $token = $request->input('token','');
            $payload = JWTAuth::setToken($token)->getPayload();
            $result['payload'] = $payload;
        } catch (TokenExpiredException $e) {
            \Log::error($e->getMessage().$e->getFile().$e->getLine());
            return $result;
        } catch (JWTException $e) {
            \Log::error($e->getMessage().$e->getFile().$e->getLine());
            return $result;
        } catch (TokenBlacklistedException $e){
            \Log::error($e->getMessage().$e->getFile().$e->getLine());
            return $result;
        } catch (TokenInvalidException $e){
            \Log::error($e->getMessage().$e->getFile().$e->getLine());
            return $result;
        }
        $result['success'] = true;
        return $result;
    }

    public function sendMail(){
       Mail::to('example@example.com')->send(new ReviewLoged(1));
    }


}