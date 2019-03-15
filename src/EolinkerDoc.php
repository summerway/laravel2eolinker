<?php
/**
 * Created by PhpStorm.
 * User: wumengchen
 * Date: 2019/1/6
 * Time: 3:33 PM
 */

namespace MapleSnow\EolinkerDoc;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EolinkerDoc
{
    public static $instance;

    public $apiName;

    public $uri;

    public $apiRequestType;

    public $apiSuccessMock;

    public $headerInfo;

    public $requestInfo;

    public $resultInfo;

    private function __construct() {}

    public static function getInstance() {
        !self::$instance && self::$instance = new self();
        return self::$instance;
    }

    public function requestRecordToDoc(Request $request) {
        $uri = $request->path();

        // 查下数据库中权限加了没
        $this->apiName = $uri;
        $headerInfo = [];
        foreach ($request->header() as $key => $header) {
            if (in_array($key, $this->headerFilter())) {
                continue;
            }
            $headerInfo[] = [
                'headerName'  => $key,
                'headerValue' => $header[0],
            ];
        }
        $requestInfo = [];
        foreach ($request->all() as $key => $value) {
            $this->requestInfoFormat($requestInfo, $key, $value);
        }
        $requestInfo = array_values($requestInfo);

        $this->uri = $uri;
        $this->headerInfo = $headerInfo;
        $this->requestInfo = $requestInfo;
        $this->apiRequestType = $this->getMethodCode($request->method());

    }

    public function headerFilter() {
        return [
            'connection',
            'accept-encoding',
            'host',
            'accept',
            'user-agent',
            'postman-token',
            'cache-control',
            'content-length'
        ];
    }

    /**
     * @param JsonResponse $response
     */
    public function responseRecordToDoc($response) {
        if (!$response instanceof JsonResponse) {
            return ;
        }
        $data = $response->getData(true);
        $resultInfo = [];
        foreach ($data as $key => $value) {
            $this->resultInfoFormat($resultInfo, $key, $value);
        }
        $resultInfo = array_values($resultInfo);
        $this->resultInfo = $resultInfo;
        $this->apiSuccessMock = json_encode($data);
    }
    /**
     * 递归处理请求参数
     * @param $resultInfo
     * @param $key
     * @param $value
     * @param string $parent
     */
    public function requestInfoFormat(&$resultInfo, $key, $value, $parent = '') {
        if (!is_int($key)) {
            $paramKey = !empty($parent) ? $parent . '::' . $key : $key;
            $resultInfo[$paramKey] = [
                'paramName'      => '',
                'paramKey'       => $paramKey,
                'paramValue'     => $value,
                'paramType'      => $this->getTypeCode($value),
                'paramLimit'     => '',
                'paramNotNull'   => 0,
                'paramValueList' => [],
            ];

            if (is_array($value) && !empty($value)) {
                foreach ($value as $k => $v) {
                    $this->requestInfoFormat($resultInfo, $k, $v, $paramKey);
                }
            }
        } else {
            if (is_array($value) && !empty($value)) {
                foreach ($value as $k => $v) {
                    $this->requestInfoFormat($resultInfo, $k, $v, $parent);
                }
            }
        }
    }

    /**
     * 递归处理返回参数
     * @param $resultInfo
     * @param $key
     * @param $value
     * @param string $parent
     */
    public function resultInfoFormat(&$resultInfo, $key, $value, $parent = '') {
        if (!is_int($key)) {
            $paramKey = !empty($parent) ? $parent . '::' . $key : $key;
            $paramValueList = [];
            $resultInfo[$paramKey] = [
                'paramName'      => '',
                'paramKey'       => $paramKey,
                'paramType'      => $this->getTypeCode($value),
                'paramNotNull'   => 0,
                'paramValueList' => $paramValueList,
            ];

            if (is_array($value) && !empty($value)) {
                foreach ($value as $k => $v) {
                    $this->resultInfoFormat($resultInfo, $k, $v, $paramKey);
                }
            }
        } else {
            if (is_array($value) && !empty($value)) {
                foreach ($value as $k => $v) {
                    $this->resultInfoFormat($resultInfo, $k, $v, $parent);
                }
            }
        }
    }

    /**
     * 在 storage/docs/下生成eolinker接口导入json
     */
    public function generateDocJson() {
        $api = [[
            'baseInfo'    => [
                'apiName'             => $this->apiName,
                'apiURI'              => $this->uri,
                'apiProtocol'         => 0,
                "apiStatus"           => 0,
                'starred'             => 0,
                'apiSuccessMock'      => $this->apiSuccessMock,
                'apiFailureMock'      => '',
                'apiRequestParamType' => 0,
                'apiRequestRaw'       => '',
                "apiNoteType"         => 0, // 0 富文本，1 markdown
                "apiNoteRaw"          => "", // 源码
                "apiNote"             => "", // html
                "apiRequestType"      => $this->apiRequestType,
                'apiUpdateTime'       => date('Y-m-d H:i:s'),

            ],
            'headerInfo'  => $this->headerInfo,
            'requestInfo' => $this->requestInfo,
            'resultInfo'  => $this->resultInfo,

        ]];

        $destination = $this->getDestinationPath(). str_replace('/', '_', $this->uri) . '.json';
        file_put_contents($destination, collect($api)->toJson(JSON_UNESCAPED_UNICODE));
    }


    private function getDestinationPath(){
        $destinationPath = storage_path('docs/');

        if(!File::exists($destinationPath)){
            File::makeDirectory($destinationPath,0777,true);
        }

        return $destinationPath;
    }

    /**
     * @param $value
     * @return int
     */
    private function getTypeCode($value) {
        $type = gettype($value);
        switch ($type) {
            case 'boolean':
                $code = 8;
                break;
            case 'integer':
                $code = 3;
                break;
            case 'double':
                $code = 5;
                break;
            case 'string':
                $code = 0;
                break;
            case 'array':
                $code = 12;
                break;
            case 'object':
                $code = 13;
                break;
            case 'resource':
                $code = 1;
                break;
            default:
                $code = 0;
        }
        return $code;
    }

    /**
     * @param $method
     * @return int
     */
    private function getMethodCode($method) {
        switch ($method) {
            case 'POST':
                $code = 0;
                break;
            case 'GET':
                $code = 1;
                break;
            case 'PUT':
                $code = 2;
                break;
            case 'DELETE':
                $code = 3;
                break;
            case 'OPTIONS':
                $code = 5;
                break;
            default:
                $code = 0;
        }
        return $code;
    }
}