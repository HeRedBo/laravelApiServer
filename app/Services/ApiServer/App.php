<?php 
namespace App\Services\ApiServer;

use App\Models\App as AppModel;
use Cache;
use Carbon\Carbon;

/**
 * API 服务端 -- App 应用相关
 *
 * @example App::getInstance('0001')->info();
 */
class App
{
    /**
     * appid
     * @var [type]
     */
    protected $app_id;

    /**
     * 缓存key 前缀
     * @var string
     */
    protected $cache_key_prefix = 'api:app:info:';

    /**
     * 初始化
     * @param string $app_id appid
     * @return object 
     */
    public function __construct($app_id)
    {
        $this->app_id = $app_id;
    }

    /**
     * 获取当前对象
     * @param string $app_id appid
     * @return object
     */
    public static function getInstance($app_id)
    {
        static $_instance = [];

        if(array_key_exists($app_id, $_instance))
            return $_instance[$app_id];

        return $_instance[$app_id] = new self($app_id);
    }

    /**
     * 获取app 信息
     * @return AppModel
     */
    public function info()
    {
        $cache_key = $this->cache_key_prefix . $this->app_id;
        if(Cache::has($cache_key))
        {
            return Cache::get($cache_key);
        }

        $app = AppModel::where(['status' => 1, 'app_id' => $this->app_id])->first();
        if($app)
            Cache::put($cache_key, $app, Carbon::now()->addMinutes(60)); // 写入缓存
        return $app;
    }
}
