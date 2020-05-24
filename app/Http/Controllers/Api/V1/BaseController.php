<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Verfication;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Dingo\Api\Exception\ValidationHttpException;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class BaseController extends Controller
{
    use Helpers;

    public $phoneZone = "852";

    public $status_bad_request = 400001;
    public $status_jwt_invalidate = 401001;

    public $status_success                           = 200;
    public $status_unkown                            = 100000; //程序内部错误：未知错误
    public $status_mysql_disconnect                = 100001; //程序内部错误: mysql 连接失败
    public $status_redis_disconnect                = 100002; //程序内部错误: redis 连接失败
    public $status_mongo_disconnect                = 100003; //程序内部错误: mongodb 连接失败

    public $message_no_permission = 'no permission';

    public $in_test_api = false;

    public static function post_url($url, $data)
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            \Log::error(curl_error($curl));//捕抓异常
            return false;
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
    }


    protected $response_data = [
        'success'=> true,
        'error_code' => 0,
        'data'   => [],
        'error_msg' => ''
    ];


    public function responseError($message,$code = 1,$status_code = 400){
        $this->response_data['success'] = false;
        $this->response_data['error_msg'] = $message;
        $this->response_data['error_code'] = $code;
        if($code == 422){
            $status_code = 422;
        }
        return response()->json($this->response_data,$status_code);
    }

    public function responseSuccess($data = array()){
        $this->response_data['success'] = true;
        $this->response_data['error_msg'] = '';
        $this->response_data['error_code'] = 0;
        $this->response_data['data'] = $data;
        return response()->json($this->response_data);
    }

    // 返回错误的请求
    protected function errorBadRequest($validator)
    {
        // github like error messages
        // if you don't like this you can use code bellow
        //
        //throw new ValidationHttpException($validator->errors());

        $errors = $validator->errors();
        if($errors){
            $firstError = $errors->first();
            return $this->responseError($firstError,$this->status_bad_request,422);
        }else{
            return $this->responseError('bad request');
        }
    }

    public function getCurrentAuthUser(){
        $user = $this->auth->user();
        return $user;
    }

    public function noneLoginResponse(){
        return $this->responseError("沒有權限訪問",$this->status_bad_request,401);
    }

    public function checkSmsCode($phone,$zone="852",$code=''){
        //驗證 sms code
        $where = [
            'verification_account'=>$phone,
            'zone' => $zone,
            'verification_code' => trim($code)
        ];
        $verification = Verfication::where($where)->first();

        if(!$verification){
            return false;
        }
        Verfication::where($where)->delete();
        return true;
    }

    public function validateJwtToken($token){
        try {
            JWTAuth::setToken($token)->invalidate();
        } catch (TokenExpiredException $e) {
            return $this->responseError('token 已超时',$this->status_jwt_invalidate,401);
        } catch (JWTException $e) {
            return $this->responseError('token 刷新失败',$this->status_jwt_invalidate,401);
        }
        return true;
    }
}