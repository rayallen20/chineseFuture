<?php
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    /**
     * 表名
     * @var string $table
     */
    protected $table = 'grade';

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
     * 本方法用于根据grade表的id字段值集合查找对应数据中 name字段值不为空的数据条数
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param array $idArr id信息集合
     * @return int $infoNum 符合条件的数据条数
    */
    public function countInfoByIdArrAndNameIsNotNull($idArr)
    {
        $infoNum = $this->whereIn('id', $idArr)
            ->whereNotNull('name')
            ->count();
        return $infoNum;
    }
}