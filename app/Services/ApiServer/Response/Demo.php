<?php
namespace App\Services\Apiserver\Response;

/**
 * api 测试类
 * @author Red-Bo
 * @date 2017-03-15 23:32:42
 */
class Demo extends BaseResponse implements InterfaceResponse
{
    
    /**
     * 接口名称
     * @var string
     */
    protected $method = 'Demo';

    /** 
     * 执行接口
     * @param array &$params 请求参数
     * @return array 
     */
    public function run(&$params)
    {
        return [
            'status' => true,
            'code' => '200',
            'data'  => [
                'current_time' => date('Y-m-d H:i:s')
            ]
        ];
    }

}