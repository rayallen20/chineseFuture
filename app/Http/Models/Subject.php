<?php
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    /**
     * 表名
     * @var string $table
     */
    protected $table = 'subject';

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
     * 科目状态 即state字段枚举值
     * @var array STATE
     * 其中: normal:科目状态正常 outOfStock:科目已售完 soldOut:下架
     */
    const STATE = [
        'normal' => 'normal',
        'outOfStock' => 'outOfStock',
        'soldOut' => 'soldOut'
    ];

    /**
     * 科目授课形式 即is_online字段值
     * @var array IS_ONLINE
     * 其中: online:线上课 offline:线下课
     */
    const IS_ONLINE = [
        'online' => 'online',
        'offline' => 'offline'
    ];

    /**
     * 本方法用于根据
     *      科目标题
     *      科目种类
     *      科目任课教师id集合
     *      科目描述
     *      科目宣传图片
     *      科目价格
     *      科目教材价格
     *      科目可售数量
     *      科目下课程数量
     *      科目授课形式
     * 保存1条信息至subject表
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param $infoArr
     * 其中:
     *      title:科目标题
     *      categoryId:科目种类
     *      teacherIds:科目任课教师id集合
     *      desc:科目描述
     *      subjectImg:科目宣传图片
     *      subjectPrice:科目价格
     *      textbookPrice:科目教材价格
     *      stock:科目可售数量
     *      lessonNum:科目下课程数量
     *      isOnline:科目授课形式
     * @return bool true表示保存成功 false表示保存失败
    */
    public function saveSubjectInfo($infoArr)
    {
        $this->title = $infoArr['title'];
        $this->grade_id = $infoArr['gradeId'];
        $this->category_id = $infoArr['categoryId'];
        $this->teacher_ids = $infoArr['teacherIds'];
        $this->desc = $infoArr['desc'];
        $this->subject_img = $infoArr['subjectImg'];
        $this->subject_price = $infoArr['subjectPrice'];
        $this->textbook_price = $infoArr['textbookPrice'];
        $this->subject_code = $infoArr['subjectCode'];
        $this->stock = $infoArr['stock'];
        $this->lesson_num = $infoArr['lessonNum'];
        $this->is_online = $infoArr['isOnline'];
        $this->state = self::STATE['normal'];
        $saveResult = $this->save();
        return $saveResult;
    }

    /**
     * 本方法用于根据科目id字段值在subject表中查找1条信息
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param int $id
     * @return \App\Http\Models\Subject|null $info 查找到信息返回该信息的ORM 否则返回null
    */
    public function findInfoById($id)
    {
        $info = $this->where('id', $id)->first();
        return $info;
    }

    /**
     * 本方法用于定义subject表和category表之间通过subject.category_id和category.id建立的1对1的关系
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function category()
    {
        return $this->hasOne('App\Http\Models\Category', 'id', 'category_id');
    }

    /**
     * 本方法用于定义subject表和grade表之间通过subject.grade_id和grade.id建立的1对1的关系
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function grade()
    {
        return $this->hasOne('App\Http\Models\Grade', 'id', 'grade_id');
    }
}