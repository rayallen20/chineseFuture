<?php
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * 表名
     * @var string $table
    */
    protected $table = 'message';

    /**
     * 主键
     * @var string $primaryKey
     */
    protected $primaryKey = 'id';

    /**
     * 是否需要在保存或修改数据时更新create_at和update_at字段
     * @var $timestamps bool
     */
    public $timestamps = true;

    /**
     * 数据创建时间字段
     * @var string CREATE_AT
     */
    const CREATED_AT = 'create_time';

    /**
     * 数据修改时间字段
     * @var string UPDATE_AT
     */
    const UPDATED_AT = 'update_time';

    /**
     * 黑名单字段
     * @var array $guarded
     */
    protected $guarded = [];

    /**
     * 短信发送结果 即send_result字段的枚举值
     * @var array SEND_RESULT
    */
    const SEND_RESULT = [
        'success' => 'success',
        'fail' => 'fail'
    ];

    /**
     * 本方法用于根据 user_id字段值 content字段值 send_result字段值 send_datetime字段值
     * 在message表中创建1条数据
     * @access public
     * @author 杨磊 <40486453@qq.com>
     * @param int $userId 用户id 即user表的id字段值
     * @param string $content 短信具体内容
     * @param string $sendResult 短信发送结果
     * @param string $sendDateTime 短息发送时间
     * @return bool $saveResult 保存结果 成功返回true 失败返回false
    */
    public function insertInfoByUserIdAndContentAndSendResultAndSendDateTime($userId, $content, $sendResult, $sendDateTime)
    {
        $this->user_id = $userId;
        $this->content = $content;
        $this->send_result = $sendResult;
        $this->send_datetime = $sendDateTime;
        $saveResult = $this->save();
        return $saveResult;
    }
}