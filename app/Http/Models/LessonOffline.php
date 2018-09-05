<?php
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LessonOffline extends Model
{
    /**
     * 表名
     * @var string $table
     */
    protected $table = 'lesson_offline';

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
     * 课程状态 即state字段枚举值
     * @var array STATE
     * 其中: notStart:未开始 inClass:上课中 finished:已过期
     */
    const STATE = [
        'notStart' => 'notStart',
        'inClass' => 'inClass',
        'finished' => 'finished'
    ];

    /**
     * 本方法用于在lesson_offline表中插入多条线下课信息并更新对应的科目信息的上课时间和下课时间
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param int $subjectId 科目信息ID
     * @param array $lessonsOffline 待插入的线下课信息数组(已经按照上课时间升序排序完毕)
     * @throws \Exception $exception 事务失败时的异常信息
     * @return bool 事务成功返回true 否则返回false
    */
    public function saveLessonsInfoAndUpdateSubjectInfo(int $subjectId, array $lessonsOffline) :bool
    {
        DB::beginTransaction();
        try
        {
            $insertResult = LessonOffline::insert($lessonsOffline);

            $updateResult = Subject::where('id', $subjectId)->update(['start_time' => $lessonsOffline[0]['start_time'], 'end_time' => end($lessonsOffline)['end_time']]);

            if($insertResult == true && $updateResult == 1)
            {
                DB::commit();
                return true;
            }
        } catch (\Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

    }
}