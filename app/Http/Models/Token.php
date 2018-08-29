<?php
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    /**
     * 表名
     * @var string $table
     */
    protected $table = 'token';

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
     * token是否有效 即is_valid字段的枚举值
     * @var array IS_VALID
     * 其中: valid:有效token invalid:无效token
    */
    const IS_VALID = [
        'valid' => 'valid',
        'invalid' => 'invalid'
    ];

    /**
     * 本方法用于在token表中根据user_id字段值和is_valid字段值查询多条数据
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param int $userId user_id字段值
     * @param string $isValid is_valid字段值
     * @return \Illuminate\Support\Collection $infos 查询到的多条数据
    */
    public function findInfoByUserIdAndIsValid($userId, $isValid)
    {
        $infos = $this
            ->where('user_id', $userId)
            ->where('is_valid', $isValid)
            ->get();
        return $infos;
    }

    /**
     * 本方法用于保存1条信息至token表
     * @access public
     * @author 杨磊 <40486453@qq.com>
     * @param int $userId user_id字段值
     * @param string $accessToken token字段值
     * @param string $grantDateTime grant_datetime字段值
     * @param string $expireDateTime expire_datetime字段值
     * @return bool $saveResult true表示保存成功 false表示保存失败
    */
    public function insertInfoByUserIdAndTokenAndDateTime($userId, $accessToken, $grantDateTime, $expireDateTime)
    {
        $this->user_id = $userId;
        $this->access_token = $accessToken;
        $this->grant_datetime = $grantDateTime;
        $this->expire_datetime = $expireDateTime;
        $this->is_valid = self::IS_VALID['valid'];
        $saveResult = $this->save();
        return $saveResult;
    }
}