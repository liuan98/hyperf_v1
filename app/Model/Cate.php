<?php

declare (strict_types=1);
namespace App\Model;

use Hyperf\DbConnection\Model\Model;
/**
 */
class Cate extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'db_cate';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * @var bool
     * 不允许添加时间戳
     */
    public $timestamps = false;

    public static $instance;

    /**
     * 通过延迟加载（用到时才加载）获取实例
     * @return self
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param $where
     * @return bool|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|null
     * 查询一条
     */
    public function findData($where){
        $data = self::query()->where($where)->first();
        if($data){
            return $data;
        }else{
            return false;
        }
    }
    /**
     * @param $where
     * @return bool|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|null
     * 查询多条
     */
    public function getList($where){
        $data = self::query()
            ->select('id','parent_id','tname')
            ->where($where)
            ->orderBy('id','ASC')
            ->orderBy('order','DESC')
            ->get();
        if($data){
            return $data;
        }else{
            return false;
        }
    }
    /**
     * @param $add
     * @return bool|int
     * 添加
     */
    public function addData($add){
        $data = self::query()->insertGetId($add);
        if($data){
            return $data;
        }else{
            return false;
        }
    }

    /**
     * @param $where
     * @param $add
     * @return bool
     * 编辑
     */
    public function editData($where,$add){
        $data = self::query()->where($where)->update($add);
        if($data !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $where
     * @return bool
     * 删除
     */
    public function delData($where){
        $data = self::query()->where($where)->delete();
        if($data){
            return true;
        }else{
            return false;
        }
    }

}