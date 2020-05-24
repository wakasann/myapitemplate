<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/24 0024
 * Time: 14:43
 */

namespace App\Listeners;

use Dingo\Api\Event\ResponseWasMorphed;

class AddFormatToResponse
{
    /**
     * 當返回http 200時，給response 添加格式
     * @param ResponseWasMorphed $event
     */
    public function handle(ResponseWasMorphed $event)
    {
        if($event->response->getStatusCode() == 200){
            $content = $event->response->getContent();
            if($content == ""){
                $content = array();
            }
            $event->response->setContent([
                'success' => true,
                'error_code' => 0,
                'data'=>$content ,
                'error_msg' => ''
            ]);
        }
    }
}