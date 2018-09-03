<?php
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    /**
     * 表名
     * @var string $table
     */
    protected $table = 'teacher';

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
     * 教师状态
     * @var array STATE
     * 其中: normal:正常状态 ban:被封禁状态
     */
    const STATE = [
        'normal' => 'normal',
        'ban' => 'ban'
    ];

    /**
     * 本方法用于保存1条教师信息至teacher表
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param string $name 教师姓名
     * @param string $mobile 教师手机号
     * @param string $headImg 教师头像
     * @param string $desc 教师简介
     * @param string $gradeIds 教师可教授的年级id集合
     * @return int|null $teacherId 保存成功返回该教师id 失败返回null
    */
    public function saveTeacherInfo($name, $mobile, $headImg, $desc, $gradeIds)
    {
        $this->name = $name;
        $this->mobile = $mobile;
        $this->head_img = $headImg;
        $this->desc = $desc;
        $this->grade = $gradeIds;
        $this->state = self::STATE['normal'];
        $saveResult = $this->save();
        if(!$saveResult)
        {
            $teacherId = null;
        }
        else
        {
            $teacherId = $this->id;
        }
        return $teacherId;
    }

    /**
     * 本方法用于根据id字段值集合在teacher表中查找对应数据的条数
     * @access public
     * @author 杨磊<40484653@qq.com>
     * @param array $idArr id字段值数组
     * @return int $infoNum 数据条数
    */
    public function countInfoByIdArr($idArr)
    {
        $infoNum = $this->whereIn('id', $idArr)
            ->where('state', self::STATE['normal'])
            ->count();
        return $infoNum;
    }

    /**
     * 本方法用于根据id字段值在teacher表中查找(IN查询)多条数据
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param array $idArr id字段值数组
     * @return \Illuminate\Support\Collection $infos 符合条件的数据ORM集合
    */
    public function findInfoInIdArr($idArr)
    {
        $infos = $this->whereIn('id', $idArr)->get();
        return $infos;
    }
}