<?php
namespace App\Services\Apiserver\Response;

/**
 * api 基础类
 * @author Red-Bo
 * @date 2017-03-15 23:29:42
 */

abstract class BaseResponse
{
    protected $method; 

    public function getMethod()
    {
        return $this->method;
    }
}