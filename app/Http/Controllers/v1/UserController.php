<?php
namespace App\Http\Controllers\v1;
use App\Http\Models\Message;
use App\Http\Models\User;
use chineseFuture\ExceptionMessage;
use chineseFuture\ServerConfig;
use chineseFuture\ServerController;
use Illuminate\Http\Request;

class UserController extends ServerController
{
    /**
     * 本方法用于发送1条验证码短信至指定手机号
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param \Illuminate\Http\Request 请求组件 实际参数为:
     * $mobile string 用户手机号
     * @return string $resultJson
    */
    public function sendVerificationCode(Request $request)
    {
        // step1. 接收参数 验证控制 start
        $mobile = $request->post('mobile');
        $paramIsNull = parent::checkParamIsNull($mobile);
        if (!$paramIsNull)
        {
            $resultJson = ExceptionMessage::generateParamNullJson();
            return $resultJson;
        }
        // step1. 接收参数 验证控制 end

        // step2. 根据用户手机号确认用户id start

        $userModel = new User();
        $userInfo = $userModel->firstOrCreateInfoByMobile($mobile);
        $userId = $userInfo->id;

        // step2. 根据用户手机号确认用户id end

        // step3. 生成短信具体内容 发送至该用户 start

        $messageTemp = ServerConfig::MESSAGE_TEMPLATE['login'];
        $code = parent::generateRandomNumStr(ServerConfig::VERIFICATION_CODE_LENGTH);
        $sendResultArr = parent::sendMessageToMobile($mobile, $messageTemp, $code);

        // step3. 生成短信具体内容 发送至该用户 end

        // step4. 将短信的具体内容 发送结果 短信接收者 存入数据库 start

        $messageSendResult = parent::getMessageSendResultBySendResult($sendResultArr['result']);
        $sendDateTime = parent::getDateTimeByTimestamp();
        $messageModel = new Message();
        $messageSaveResult = $messageModel->insertInfoByUserIdAndContentAndSendResultAndSendDateTime($userId, $sendResultArr['content'], $messageSendResult, $sendDateTime);
        if(!$messageSaveResult)
        {
            $resultJson = ExceptionMessage::generateSaveFailJson($messageModel->getTable());
            return $resultJson;
        }

        // step4. 将短信的具体内容 发送结果 短信接收者 存入数据库 end

        // step5. 生成返回json start

        $data[] = [
            'sendResult' => $sendResultArr['result'],
        ];
        $resultJson = ExceptionMessage::generateSuccessJson($data);
        return $resultJson;

        // step5. 生成返回json end
    }


}