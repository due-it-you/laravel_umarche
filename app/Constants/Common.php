<?php 

namespace App\Constants;

class Common 
{
    //在庫管理での『追加(type=1)』と『削減(type=2)』を定数化してマジックナンバー回避する
    const PRODUCT_ADD = '1';
    const PRODUCT_REDUCE = '2';

    //更に纏める。
    const PRODUCT_LIST = [
        'add' => self::PRODUCT_ADD,
        'reduce' => self::PRODUCT_REDUCE,
    ];
}