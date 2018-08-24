<?php
namespace App\Http\Controllers\v1;
use App\Http\Models\Message;
use App\Http\Models\Token;
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

        // step5. 更新该用户的当前验证码 start

        $saveCodeResult = $userInfo->saveMsgCode($userInfo, $code);
        if(!$saveCodeResult)
        {
            $resultJson = ExceptionMessage::generateSaveFailJson($userModel->getTable());
            return $resultJson;
        }

        // step5. 更新该用户的当前验证码 end

        // step6. 生成返回json start

        $data[] = [
            'sendResult' => $sendResultArr['result'],
        ];
        $resultJson = ExceptionMessage::generateSuccessJson($data);
        return $resultJson;

        // step6. 生成返回json end
    }

    /**
     * 本方法用于用户根据密码注册并发放token
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param \Illuminate\Http\Request $request 请求组件 实际参数为:
     * $mobile string 用户手机号
     * $password string 用户设置的密码
     * $confirmPassword string 用户再次输入的确认密码
     * @return string $resultJson 返回json
    */
    public function registerByPassword(Request $request)
    {
        // step1. 接收参数 验证空值 start
        $mobile = $request->post('mobile');
        $password = $request->post('password');
        $confirmPassword = $request->post('confirmPassword');
        $paramIsNull = parent::checkParamIsNull($mobile, $password, $confirmPassword);
        if(!$paramIsNull)
        {
            $resultJson = ExceptionMessage::generateParamNullJson();
            return $resultJson;
        }
        // step1. 接收参数 验证空值 end

        // step2. 确认该手机号在user表中是否存在 如果已存在 则不能注册 start
        $userModel = new User();
        $userInfo = $userModel->firstOrCreateInfoByMobile($mobile);
        if(!$userInfo->wasRecentlyCreated)
        {
            $resultJson = ExceptionMessage::generateMobileHasExistJson();
            return $resultJson;
        }
        // step2. 确认该手机号在user表中是否存在 如果已存在 则不能注册 end

        // step3. 确认2次输入的密码是否一致 start
        if($password != $confirmPassword)
        {
            $resultJson = ExceptionMessage::generateParamNullJson();
            return $resultJson;
        }
        // step3. 确认2次输入的密码是否一致 end

        // step4. 保存用户密码 start
        $password = parent::md5EncryptStr($password);
        $savePasswordResult = $userInfo->savePassword($userInfo, $password);
        if(!$savePasswordResult)
        {
            $resultJson = ExceptionMessage::generateSaveFailJson($userModel->getTable());
            return $resultJson;
        }
        // step4. 保存用户密码 end

        // step5. 为该用户生成token start
        $userId = $userInfo->id;
        $token = self::generateTokenForUser($userId, $mobile);
        if(!$token)
        {
            $tokenModel = new Token();
            $resultJson = ExceptionMessage::generateSaveFailJson($tokenModel->getTable());
            return $resultJson;
        }
        // step5. 为该用户生成token end

        // step6. 生成返回json start
        $data[] = [
            'userId' => $userId,
            'accessToken' => $token
        ];
        $resultJson = ExceptionMessage::generateSuccessJson($data);
        return $resultJson;
        // step6. 生成返回json end
    }

    /**
     * 本方法用于根据手机号为指定的用户ID为该用户生成1个token
     * @access private
     * @author 杨磊<40486453@qq.com>
     * @param int $userId 指定的用户ID
     * @param string $mobile 生成token用的手机号
     * @return string|false 生成成功返回token值 失败返回false
     */
    private function generateTokenForUser($userId, $mobile)
    {
        $token = parent::generateToken($mobile);
        $grantDateTime = parent::getDateTimeByTimestamp();
        $expireDateTime = parent::generateDiffDayDateTime(ServerConfig::TOKEN_VALID_DAY);
        $tokenModel = new Token();
        $saveTokenResult = $tokenModel->insertInfoByUserIdAndTokenAndDateTime($userId, $token, $grantDateTime, $expireDateTime);
        if(!$saveTokenResult)
        {
            return false;
        }
        return $token;
    }

    /**
     * 本方法用于用户根据短信验证码登录并发放token
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param \Illuminate\Http\Request $request 请求组件 实际参数为:
     * $mobile string 用户手机号
     * $msgCode string 用户输入的验证码
     * @return string $resultJson 返回的json
    */
    public function loginByMsgCode(Request $request)
    {
        // step1. 接收参数 验证空值 start
        $mobile = $request->post('mobile');
        $msgCode = $request->post('msgCode');
        $paramIsNull = parent::checkParamIsNull($mobile, $msgCode);
        if(!$paramIsNull)
        {
            $resultJson = ExceptionMessage::generateParamNullJson();
            return $resultJson;
        }
        // step1. 接收参数 验证空值 end

        // step2. 判断手机号和验证码是否正确 start
        $userModel = new User();
        $userInfo = $userModel->findInfoByMobileAndMsgCode($mobile, $msgCode);
        if(is_null($userInfo))
        {
            $resultJson = ExceptionMessage::generateMobileOrMsgCodeDoesNotExistJson();
            return $resultJson;
        }
        // step2. 判断手机号和验证码是否正确 end

        // step3. 为该用户生成token start
        $userId = $userInfo->id;
        $token = self::generateTokenForUser($userId, $mobile);
        if(!$token)
        {
            $tokenModel = new Token();
            $resultJson = ExceptionMessage::generateSaveFailJson($tokenModel->getTable());
            return $resultJson;
        }
        // step3. 为该用户生成token end

        // step4. 生成返回json start
        $data[] = [
            'userId' => $userId,
            'accessToken' => $token
        ];
        $resultJson = ExceptionMessage::generateSuccessJson($data);
        return $resultJson;
        // step4. 生成返回json end
    }
}