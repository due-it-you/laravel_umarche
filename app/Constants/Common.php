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

    //表示順　判定のための定数
    const ORDER_RECOMMEND = '0';
    const ORDER_HIGHER = '1';
    const ORDER_LOWER = '2';
    const ORDER_LATER = '3';
    const ORDER_OLDER = '4';

    const SORT_ORDER = [
        'recommend' => self::ORDER_RECOMMEND,
        'higherPrice' => self::ORDER_HIGHER,
        'lowerPrice' => self::ORDER_LOWER,
        'later' => self::ORDER_LATER,
        'older' => self::ORDER_OLDER,
    ];
}