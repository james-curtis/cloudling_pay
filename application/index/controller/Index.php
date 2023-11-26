<?php
namespace app\index\controller;

use think\Request;

class Index extends Base
{
    public function index()
    {
        $this->set['user_count'] = db('user') -> count();
        $this->set['order_count'] = db('order') -> count();
        $this->set['money_count'] = db('order') -> where('status',2) -> count('money');
        $this->set['today_money_count'] = db('order') -> whereTime('endtime','today') ->  where('status' , 2) -> count('money');
        $this->assign('set',$this->set);
        return view();
    }

    public function agreement()
    {
        return view();
    }

}
