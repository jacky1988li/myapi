<?php

class Btoken
{
    /**
     * 浏览器接口 token验证
     * @param  Request  $request
     * @param Response $response
     * @return mixed
     */
    public function handle($request, $response)
    {
        $sign = $request->input('sign', '');
        $apptoken = $request->input('apptoken', '');

        $media = (new DB)->table('medias')->take(1)->where('f_apptoken', $apptoken)->first();

        if (!$media) {
            $response->json(['code' => 103, 'msg' => '签名认证失败']);
        }

        //var_dump($sign, $media->f_appkey, $apptoken, md5($media->f_appkey . $apptoken));die;

        if (strcmp($sign, md5($media->f_appkey . $apptoken)) != 0) {
            $response->json(['code' => 103, 'msg' => '签名认证失败']);
        }
        // 设置ssp的apptoken和appkey
        $request->offsetSet('media_id', $media->f_id);
        $request->offsetSet('company_id', $media->f_company_id);
        $request->offsetSet('ssptoken', $media->f_ssptoken);
        $sign = md5($media->f_sspkey . $media->f_ssptoken);
        $request->offsetSet('sspsign', $sign);
        return true;
    }
}
