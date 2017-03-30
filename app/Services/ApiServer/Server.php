<?php 
namespace App\Services\ApiServer;


use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests;

/**
 * API服务总入口
 * @author RedBo 
 */
class Server
{
    /**
     * 请求参数
     * @var array
     */
    protected $params = [];

    /**
     * API请求的Method名
     * @var string
     */
    protected $method;

    /**
     * app_secret
     * @var string
     */
    protected  $app_secret;

    /**
     * 回调格式数据
     * @var string
     */
    protected $format = 'json';

    /**
     * 签名方式
     * @var string
     */
    protected $sign_method  = 'md5';

    /**
     * 是否输出错误码
     * @var boolean
     */
    protected $error_code_show = false;

    /**
     *初始化
     * @param Error $error Error 对象
     */
    public function __construct(Error $error)
    {
        $this->params = Request::all();
        $this->error  = $error;
    }

    /**
     * api 服务入口执行
     * @param Request $reqest 请求的参数
     * @return [type] [description]
     */
    public function run()
    {
        // A.1 初步校验
        $rules = [
            'app_id'      => 'required',
            'method'      => 'required',
            'format'      => 'in:,json',
            'sign_method' => 'in:,md5',
            'nonce'       => 'required|string|min:1|max:32',
            'sign'        => 'required',
        ];

        $messages = [
            'app_id.required' => '1001',
            'method.required' => '1003',
            'format.in'       => '1004',
            'sign_method.in'  => '1005',
            'nonce.required'  => '1010',
            'nonce.string'    => '1012',
            'nonce.min'       => '1012',
            'nonce.max'       => '1012',
            'sign.required'   => '1006',
        ];

        $v = Validator::make($this->params, $rules, $messages);
        if($v->fails())
        {
            return  $this->response(['status' => false,'code' => $v->message()->first()]);
        }

        // A.2  赋值对象
        $this->format      = !empty($this->params['format']) ? $this->params['format'] : $this->format;
        $this->sign_method = !empty($this->params['sign_method']) ? $this->params['sign_method'] : $this->sign_method;
        $this->app_id      = $this->params['app_id'];
        $this->method      = $this->params['method'];

        // B. appid 校验 
        $app = App::getInstance($this->app_id)->info();
        if( ! $app)
            return $this->response(['status'=> false, 'code' => '1002']);
        $this->app_secret = $app->app_secret;

        // C. 校验签名
        $signRes = $this->checkSign($this->params);
        if (!$signRes || !$signRes['status']) {
            return $this->response(['status' => false, 'code' => $signRes['code']]);
        }

        // D. 校验接口名
        // D.1 通过方法名获取类名 
        $className = self::getClssName($this->method);

        // D.2 判断类名是否存在
        $classPath = __NAMESPACE__. 'Response\\'.$className;

        // D.3 判断方法名是否存在
        if(!method_exists($classPath, 'run')) {
            return $this->response(['status' => false, 'code' => '1009']);
        }

        $this->classname = $classPath;

        // E. api 接口分发
        $class = new $classPath;
        return $this->response((array) $class->run($this->params));
    }

    /**
     * 校验签名
     * @param  arrray $params 请求参数
     * @return array 
     */
    protected function checkSign($params)
    {
        $sign = array_key_exists('sign', $params) ? $params['sign'] : '';

        if(empty($sign))
            return ['status' => false, 'code' => '1006'];
        unset($params['sign']);

        if($sign != $this->generateSign($params))
        {
            return ['status' => false, 'code' => '1007'];
        }
        return ['status' => true, 'code' => '200'];
    }

    /**
     * 生成签名
     * @param array $params 待校验的参数
     * @return string|false
     */
    protected function generateSign($params)
    {
        if($this->sign_method == 'md5')
            return $this->generateMd5Sign($params);
        return false;
    }

    /**
     * md5方式加密
     * @param  array $params 待签名参数
     * @return string
     */
    protected function generateMd5Sign($params)
    {
        ksort($params);

        $tmps = [];
        foreach ($params as $k => $v) 
        {
            $tmps[] = $k . $v;
        }

        $string = $this->app_secret . implode('', $tmps) . $this->app_secret;
        return strtoupper(md5($string));
    }

    /**
     * 通过方法名转换为对应的类名
     * @param  string $method 方法名
     * @return string|false
     */
    protected static function getClassName($method)
    {
        $methods = explode('.', $method);
        if(!is_array($methods))
            return false;

        $tmp = [];
        foreach ($methods as $key => $value) 
        {
            $tmp[] = ucwords($value);
        }
        return implode('',$tmp);
    }

    /**
     * 输出结果
     * @param  array  $result 结果
     * @return response
     */
    protected function response(array $result)
    {
        if (! array_key_exists('msg', $result) && array_key_exists('code', $result))
        {
            $result['msg'] = $this->getError($result['code']);
        }

        if($this->format == 'json') 
        {
            return response()->josn($result);
        }

        return false;
    }

    /**
     * 返回错误信息
     * @param  string $code 错误码
     * @return string
     */
    protected function getError($code)
    {
        return $this->error->getError($code,$this->error_code_show);
    }
}
