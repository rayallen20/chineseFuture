<?php
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 表名
     * @var string $table
    */
    protected $table = 'user';

    /**
     * 主键
     * @var string $primaryKey =
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
     * 数据创建时间和数据修改时间字段的时间格式
     * @var string $dateFormat
    */
    protected $dateFormat = 'Y-m-d H:i:s';


    /**
     * 黑名单字段
     * @var array $guarded
     */
    protected $guarded = [];

    /**
     * 默认头像地址
     * @var string DEFAULT_HEAD_PIC
    */
    const DEFAULT_HEAD_PIC = 'http://www.codingfat.com/defaultHeaderImg.jpeg';

    /**
     * 用户性别
     * @var array GENDER
     * 其中: male:男 female:女 unknown:未知性别
    */
    const GENDER = [
        'male' => 'male',
        'female' => 'female',
        'unknown' => 'unknown'
    ];

    /**
     * 账户初始余额
     * @var int INIT_BALANCE
    */
    const INIT_BALANCE = 0;

    /**
     * 用户状态
     * @var array STATE
     * 其中:normal:正常 ban:封禁
    */
    const STATE = [
        'normal' => 'normal',
        'ban' => 'ban'
    ];

    /**
     * 本方法用于根据手机号保存1条信息至user表
     * @access public
     * @author 杨磊 <40486453@qq.com>
     * @param string $mobile 用户手机号
     * @return \App\Http\Models\User|false 保存成功返回该条信息ORM 否则返回false
    */
    public function saveInfoByMobile($mobile)
    {
        $info = new self();
        $info->mobile = $mobile;
        $saveResult = $info->save();
        if(!$saveResult)
        {
            return false;
        }
        return $info;
    }

    /**
     * 本方法用于根据手机号查找或创建1条信息至user表
     * @access public
     * @author 杨磊<40484653@qq.com>
     * @param string $mobile 用户手机号
     * @return \Illuminate\Database\Eloquent\Model user表信息的ORM
    */
    public function firstOrCreateInfoByMobile($mobile)
    {
        $arr = [
            'mobile' => $mobile,
        ];
        $info = $this->firstOrCreate($arr);
        return $info;
    }
}