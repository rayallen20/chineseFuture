<?php
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

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


}