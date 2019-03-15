<?php
/**
 * Created by PhpStorm.
 * User: MapleSnow
 * Date: 2019/3/15
 * Time: 2:05 PM
 */

namespace MapleSnow\EolinkerDoc;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Closure;


class HandleRequest {

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            $doc = EolinkerDoc::getInstance();
            $doc->requestRecordToDoc($request);
            $response = $next($request);

            $doc->responseRecordToDoc($response);
            $doc->generateDocJson();
        }catch (\Exception $e){
            Log::error("Generate Eolinker doc failed:".$e->getMessage());
            $response = $next($request);
        }

        return $response;
    }
}