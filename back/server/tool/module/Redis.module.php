<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/7
 * @Time: 11:25
 */

namespace tool\module;


class Redis
{
    public static function connect()
    {
        try {
            $redis = new \Redis();
            $redis->open(REDIS_URL, REDIS_PORT);
            $redis->auth(REDIS_REQUIREPASS);
            $redis->select(0);
            return $redis;
        } catch (\Exception $e) {
            return false;
        }
    }
}