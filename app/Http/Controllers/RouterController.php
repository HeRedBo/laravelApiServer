<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Services\ApiServer\Server;
use App\Services\ApiServer\Error;

/**
 * Api 入口控制器
 */
class RouterController extends Controller
{
    /**
     * API总入口文件
     */
    public function index()
    {
        $server = new Server(new Error);
        return $server->run();
    }
}
