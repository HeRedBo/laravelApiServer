<?php
namespace App\Services\Apiserver\Response;

/**
 * api接口类
 * @author Red-Bo
 * @date 2017-03-15 23:24:59
 */
interface InterfaceResponse
{
    /**
     * 执行接口
     * @return array
     */
    public function run();

    /**
     * 返回接口名称
     * @return string
     */
    public function getMethod();
}