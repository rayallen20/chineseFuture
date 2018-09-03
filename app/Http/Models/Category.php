<?php
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * 表名
     * @var string $table
     */
    protected $table = 'category';

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
     * 本方法用于查找Category表中所有id字段值
     * @access public
     * @author 杨磊<40486453@qq.com>
     * @param void
     * @return \Illuminate\Support\Collection $infos 全部id字段值集合
    */
    public function findAllCategoryId()
    {
        $infos = $this->select('id')->get();
        return $infos;
    }
}