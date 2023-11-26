<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/2/1
 * Time: 18:14
 */

namespace app\index\model;


use think\Model;

class Order extends Model
{
    protected $name = 'order';

    protected $updateTime = false;

    protected $createTime = 'createtime';

    protected $insert = [
        'domain',
        'ip',
        'status' => 0,
        'trade_no',
    ];

    protected function setCreatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    protected function setDomainAttr()
    {
        return get_domain($this->data['notify_url']);
    }

    protected function setIpAttr()
    {
        return request() -> ip();
    }

    protected function setTradeNoAttr()
    {
        return date("YmdHis") . rand(11111, 99999);
    }

    protected function scopeLikeName($query,$name)
    {
        return $query -> where('name','like','%'.$name.'%');
    }

    protected function scopeColumn($query,$column,$value)
    {
        return $query -> where($column,$value);
    }

    protected function scopeCount($query)
    {
        return $query;
    }

}