<?php

class InfoController extends BaseController{
    const REQ_TYPE_API = 1;
    const REQ_TYPE_JS = 2;
    const REQ_TYPE_H5 = 3;

    private $cat_id_relations = [
        // 旺翔
        99999 => '1001,1002,1006,1009,1013,1014,1016,1021,1024',
        // 新网程
        // 99981 => '1021,1002,1006,1012', //要闻：时政、体育、财经、军事
        // 99982 => '1008,1014,1015,1016,1027', //生活：房产、健康、母婴、社会、旅游
        // 99983 => '1001,1011,1009,1017,1007,1031', //文娱：娱乐、文化、时尚、美食、汽车、动漫
        // 99984 => '1005,1013,1019,1026', //科技：手机、科技、游戏、猎奇
    ];

    // 计算catid
    protected function calculateCatId($catId)
    {
        if (in_array($catId, array_keys($this->cat_id_relations))) {
            $catId = $this->cat_id_relations[$catId];
        }
        return $catId;
    }


    public function sendJsonPost($url, $jsonStr, $headers = ["content-type: application/json"], $useragent='')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($useragent) {
            curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        }
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        // curl_setopt($ch, CURLOPT_TIMEOUT_MS, config('app.curl_timeout_ms'));

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); //强制使用IPV4协议解析域名
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * 获取xd信息流[给APP调用]
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    /*
    public function getXdInfo(Request $request, Response $response)
    {
        $catId = intval($request->input('catId', 1001));
        $pageIndex = intval($request->input('pageIndex', 1));
        $userAgent = $request->server('HTTP_USER_AGENT');

        $osType = $request->input('osType', 1);
        $fakeids = $this->getFakeIds($osType);
        $post = [
            // 使用协议版本,目前 1.0
            'version'        => '1.0',
            // 签名,目前不支持
            'sign'           => '',
            // 渠道 ID，请联系商务提供
            'channelId'      => config('app.xd_config.channelId'),
            // 应用 ID，请联系商务提供
            'appId'          => config('app.xd_config.appId'),
            // 广告位 id，请联系商务提供
            'slotId'         => config('app.xd_config.slotId'),
            // 设备类型[0:未知 UNKNOWN 1:手机 PHONE 2:平板 TABLET]
            'deviceType'     => $request->input('deviceType', 1),
            // 系统类型 [UNKNOWN = 0; //未知 ANDROID = 1; IOS = 2; WP = 3;]
            'osType'         => $osType,
            // 系统版本号,格式:a.b.c
            'osVersion'      => $request->input('osVersion', '0.0.0'),
            // ios 设备必填
            'idfa'           => $request->input('idfa') ? $request->input('idfa') : $fakeids->f_idfa,
            // IMEI 号(重要,必传)
            'imei'           => $request->input('imei') ? $request->input('imei') : $fakeids->f_imei,
            // IMSI 号(重要,必传)
            'imsi'           => $request->input('imsi') ? $request->input('imsi') : getFakeImsi(),
            // MAC 地址
            'mac'            => $request->input('mac') ? $request->input('mac') : getFakeMacAddress(),
            // Android ID 手机唯一标识
            'androidId'      => $request->input('androidId') ? $request->input('androidId') : $fakeids->f_androidid,
            // 设备型号
            'model'          => $request->input('model', ''),
            // 厂商名称
            'vendor'         => $request->input('vendor', ''),
            // 屏幕宽(px)
            'screenWidth'    => $request->input('screenWidth', ''),
            // 屏幕高(px)
            'screenHeight'   => $request->input('screenHeight', ''),
            // 设备品牌 如 OPPO
            'brand'          => $request->input('brand', ''),
            // 浏览器 UA
            'userAgent'      => $userAgent,
            // 客户端 IPv4 地址(重要,必传)
            'ip'             => $request->input('ip') ? $request->input('ip') : get_client_ip(),
            // 网络类型[0:UNKNOWN 1:CELL_UNKNOWN 2:2G 3:3G 4:4G 5:5G 100:WIFI 101:ETHERNET 999:NEW_TYPE]
            'connectionType' => $request->input('connectionType', 0),
            // 运营商类型[0:UNKNOWN 1:CHINA_MOBILE 2:CHINA_TELECOM 3:CHINA_UNICOM 99:OTHER_OPERATOR]
            'operatorType'   => $request->input('operatorType', 0),
            // 需要的数据条数，最大 20 条
            'pageSize'       => config('app.info_count'),
            // 'pageSize'       => 1,
            // 分页页码
            'pageIndex'      => $pageIndex,
            // 需要的广告条数,最大 10 条
            'adCount'        => config('app.ad_count'),
            // 分类 id
            'catId'          => $catId
        ];
        $url = config('app.xd_config.url');
        $jsonStr = json_encode($post);
        $rep = $this->sendJsonPost($url, $jsonStr);
        $rep = json_decode($rep);
        if ($rep->code != 0) {
            $response->json(['code' => $rep->code, 'msg' => $rep->msg]);
        }
        $ssptoken = $request->input('ssptoken');
        $sspsign = $request->input('sspsign');
        $param = array_merge($post, [
            'ssptoken'=> $ssptoken,
            'sspsign' => $sspsign,
            'adType' => 4
        ]);

        $sspApiAds = [];
        $ad1 = $this->getSspApiAds($param);
        if (0 == $ad1['error_code']) {
            array_push($sspApiAds, $ad1);
        }

        $ad2 = $this->getSspApiAds($param);
        if (0 == $ad2['error_code']) {
            array_push($sspApiAds, $ad2);
        }

        $data = $this->dealXdReturn($rep, $catId, $pageIndex);
        unset($rep);

        // $cpaAds = $this->getCpaAds($catId);
        // if (401 == $cpaAds) {
            // $response->json(['code'=> 401, 'msg'=>'CPA AD 返回失败']);
        // }

        $data = array_merge($data, $sspApiAds);
        // $data = array_merge($data, $cpaAds);
        $data = $this->fillWithAds($data);

        $total = count($data);

        $this->recordRequestLog(array_merge($post, [
            'media_id'=> $request->input('media_id'),
            'req_type' => self::REQ_TYPE_API
        ]));

        $response->json(['code' => 200, 'msg' => '返回成功', 'total' => $total, 'data' => $data]);
    }
     */
    /**
     * 获取xd信息流[给 外部 APP调用]
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response

    public function getXdInfoForExternal(Request $request, Response $response)
    {
        //
            // sign
            // apptoken
            // data
                // catid
                // page_index
                // device_type
                // os_type
        //

        $params = $request->input("data");
        if (empty($params)) {
            $response->json(['code'=> 104, 'msg'=>'业务参数data缺失']);
        }

        $catId     = isset($params['catid']) ? $params['catid'] : '';
        $pageIndex = isset($params['page_index']) ? $params['page_index'] : '';
        $deviceType = isset($params['device_type']) ? $params['device_type'] : '';
        $osType = isset($params['os_type']) ? $params['os_type'] : '';
        if (!($catId && $pageIndex && $deviceType && $osType)) {
            $response->json(['code'=> 104, 'msg'=>'业务参数data缺失']);
        }

        $fakeids = $this->getFakeIds($osType);

        $osVersion = isset($params['os_version']) ? $params['os_version'] : '7.0.0';
        $idfa = isset($params['idfa']) ? $params['idfa'] : $fakeids->f_idfa;
        $imei = isset($params['imei']) ? $params['imei'] : $fakeids->f_imei;
        $imsi = isset($params['imsi']) ? $params['imsi'] : getFakeImsi();
        $mac = isset($params['mac']) ? $params['mac'] : getFakeMacAddress();
        $androidId = isset($params['android_id']) ? $params['android_id'] : $fakeids->f_androidid;

        $model = isset($params['model']) ? $params['model'] : 'iPhone 6S';
        $vendor = isset($params['vendor']) ? $params['vendor'] : 'APPLE';
        $screenWidth = isset($params['screen_width']) ? $params['screen_width'] : 640;
        $screenHeight = isset($params['screen_height']) ? $params['screen_height'] : 1136;
        $brand = isset($params['brand']) ? $params['brand'] : '';
        $ip = isset($params['ip']) ? $params['ip'] : getFakeIP();
        $connectionType = isset($params['connection_type']) ? $params['connection_type'] : 0;
        $operatorType = isset($params['operator_type']) ? $params['operator_type'] : 0;

        unset($params);

        $userAgent = $request->server('HTTP_USER_AGENT');

        $post = [
            // 使用协议版本,目前 1.0
            'version'        => '1.0',
            // 签名,目前不支持
            'sign'           => '',
            // 渠道 ID，请联系商务提供
            'channelId'      => config('app.xd_config.channelId'),
            // 应用 ID，请联系商务提供
            'appId'          => config('app.xd_config.appId'),
            // 广告位 id，请联系商务提供
            'slotId'         => config('app.xd_config.slotId'),
            // 设备类型[0:未知 UNKNOWN 1:手机 PHONE 2:平板 TABLET]
            'deviceType'     => $deviceType,
            // 系统类型 [UNKNOWN = 0; //未知 ANDROID = 1; IOS = 2; WP = 3;]
            'osType'         => $osType,
            // 系统版本号,格式:a.b.c
            'osVersion'      => $osVersion,
            // ios 设备必填
            'idfa'           => $idfa,
            // IMEI 号(重要,必传)
            'imei'           => $imei,
            // IMSI 号(重要,必传)
            'imsi'           => $imsi,
            // MAC 地址
            'mac'            => $mac,
            // Android ID 手机唯一标识
            'androidId'      => $androidId,
            // 设备型号
            'model'          => $model,
            // 厂商名称
            'vendor'         => $vendor,
            // 屏幕宽(px)
            'screenWidth'    => $screenWidth,
            // 屏幕高(px)
            'screenHeight'   => $screenHeight,
            // 设备品牌 如 OPPO
            'brand'          => $brand,
            // 浏览器 UA
            'userAgent'      => $userAgent,
            // 客户端 IPv4 地址(重要,必传)
            'ip'             => $ip,
            // 网络类型[0:UNKNOWN 1:CELL_UNKNOWN 2:2G 3:3G 4:4G 5:5G 100:WIFI 101:ETHERNET 999:NEW_TYPE]
            'connectionType' => $connectionType,
            // 运营商类型[0:UNKNOWN 1:CHINA_MOBILE 2:CHINA_TELECOM 3:CHINA_UNICOM 99:OTHER_OPERATOR]
            'operatorType'   => $operatorType,
            // 需要的数据条数，最大 20 条
            'pageSize'       => config('app.info_count'),
            // 'pageSize'       => 1,
            // 分页页码
            'pageIndex'      => $pageIndex,
            // 需要的广告条数,最大 10 条
            'adCount'        => config('app.ad_count'),
            // 分类 id
            'catId'          => $catId
        ];

        $url = config('app.xd_config.url');
        $jsonStr = json_encode($post);
        $rep = $this->sendJsonPost($url, $jsonStr);
        $rep = json_decode($rep);
        if ($rep->code != 0) {
            $response->json(['code' => $rep->code, 'msg' => $rep->msg]);
        }

        $ssptoken = $request->input('ssptoken');
        $sspsign = $request->input('sspsign');
        $param = array_merge($post, [
            'ssptoken'=> $ssptoken,
            'sspsign' => $sspsign,
            'adType' => 4
        ]);
        $ad1 = $this->getSspApiAds($param);
        $sspApiAds = [];
        if (0 == $ad1['error_code']) {
            array_push($sspApiAds, $ad1);
        }

        $ad2 = $this->getSspApiAds($param);
        if (0 == $ad2['error_code']) {
            array_push($sspApiAds, $ad2);
        }

        $data = $this->dealXdReturn($rep, $catId, $pageIndex);
        unset($rep);
        $data = array_merge($data, [$ad1, $ad2]);
        $data = $this->fillWithAds($data);

        $total = count($data);

        $this->recordRequestLog(array_merge($post, [
            'media_id'=> $request->input('media_id'),
            'req_type' => self::REQ_TYPE_API
        ]));

        $response->json(['code' => 200, 'msg' => '返回成功', 'total' => $total, 'data' => $data]);
    }
    */


    /**
     * 获取xd信息流[给外部H5页面调用]
     * 目前有提供 新网程 调用
     *
     * wifi   提供参数 catid      page_index     device_type    os_type  country
     *                 province   city           county         ip       useragent
     *                 browser    screen_width   screen_height  mac      os_version
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response

    public function getH5XdInfoForExternal(Request $request, Response $response)
    {
        $media_id = $request->input('media_id');
        $data = $request->input("data");
        if (empty($data)) {
            $response->json(['code'=> 104, 'msg'=>'业务参数data缺失']);
        }
        // 新网程
        if ($media_id == 5) {
            //检查必填参数
            $required = [
                'catid','page_index','device_type','os_type','country',
                'province','city','county','ip','useragent',
                'browser','screen_width','screen_height','mac','os_version'
            ];
            foreach($required as $req) {
                if (isset($data[$req])) {
                    continue;
                } else {
                    $response->json(['code'=> 104, 'msg'=>'业务参数data缺失,请检查 ' . $req . ' 参数。']);
                }
            }
        }

        if (!empty($data['useragent'])) {
            $userAgent = $data['useragent'];
        } else {
            $userAgent = $request->server('HTTP_USER_AGENT');
        }

        $uaExtra = get_mobile_ua_extra($userAgent);

        $catId     = $data['catid'];

        $pageIndex = $data['page_index'];


        if (in_array(intval($data['device_type']), [0,1,2,3])) {
            $deviceType = $data['device_type'];
        } else {
            $deviceType = $uaExtra['deviceType'];
        }

        if (in_array(intval($data['os_type']), [0,1,2,3])) {
            $osType = $data['os_type'];
        } else {
            $osType = $uaExtra['osType'];
        }

        if (!empty($data['os_version'])) {
            $osVersion = $data['os_version'];
        } else {
            $osVersion = $uaExtra['osVersion'];
        }

        if (!empty($data['mac'])) {
            $mac = $data['mac'];
        } else {
            $mac = getFakeMacAddress();
        }

        $screenWidth = !empty($data['screen_width']) ? $data['screen_width'] : 640;
        $screenHeight = !empty($data['screen_height']) ? $data['screen_height'] : 1136;



        if (!empty($data['ip']) && $data['ip']) {
            $ip = $data['ip'];
            $filter = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
            if (!filter_var($ip, FILTER_VALIDATE_IP, $filter)) {
                $response->json(['code'=> 104, 'msg'=>'业务参数data 中的 ip 不正确，必须是公网ip。']);
            }
        } else {
            $ip = get_client_ip();
        }

        $country  = !empty($data['country']) ? $data['country'] : '';
        $province = !empty($data['province']) ? $data['province'] : '';
        $city     = !empty($data['city']) ? $data['city'] : '';
        $county   = !empty($data['county']) ? $data['county'] : '';
        // $gender   = !empty($data['gender']) ? $data['gender'] : '';
        // $unionid  = !empty($data['unionid']) ? $data['unionid'] : '';

        $browser  = !empty($data['browser']) ? $data['browser'] : '';

        // 从cpa request 表中拿 idfa
        // 从t_user 表拿 udid  as imei , androidid

        $fakeids = $this->getFakeIds($osType);

        // h5需要提供参数 catId, pageIndex
        $post = [
            // 使用协议版本,目前 1.0
            'version'        => '1.0',
            // 签名,目前不支持
            'sign'           => '',
            // 渠道 ID，请联系商务提供
            'channelId'      => config('app.xd_config.channelId'),
            // 应用 ID，请联系商务提供
            'appId'          => config('app.xd_config.appId'),
            // 广告位 id，请联系商务提供
            'slotId'         => config('app.xd_config.slotId'),
            // 设备类型[0:未知 UNKNOWN 1:手机 PHONE 2:平板 TABLET]
            'deviceType'     => $deviceType,
            // 系统类型 [UNKNOWN = 0; //未知 ANDROID = 1; IOS = 2; WP = 3;]
            'osType'         => $osType,
            // 系统版本号,格式:a.b.c
            'osVersion'      => $osVersion,
            // ios 设备必填
            'idfa'           => $fakeids->f_idfa,
            // IMEI 号(重要,必传)
            'imei'           => $fakeids->f_imei,
            // IMSI 号(重要,必传)
            'imsi'           => getFakeImsi(),
            // MAC 地址（随机伪造）
            'mac'            => $mac,
            // Android ID 手机唯一标识
            'androidId'      => $fakeids->f_androidid,
            // 设备型号
            'model'          => $request->input('model', ''),
            // 厂商名称
            'vendor'         => $uaExtra['brand'],
            // 屏幕宽(px)
            'screenWidth'    => $screenWidth ? $screenWidth : 640,
            // 屏幕高(px)
            'screenHeight'   => $screenHeight ? $screenHeight : 1136,
            // 设备品牌 如 OPPO
            'brand'          => $uaExtra['brand'],
            // 浏览器 UA
            'userAgent'      => $userAgent,
            // 客户端 IPv4 地址(重要,必传)
            'ip'             => $ip,
            // 网络类型[0:UNKNOWN 1:CELL_UNKNOWN 2:2G 3:3G 4:4G 5:5G 100:WIFI 101:ETHERNET 999:NEW_TYPE]
            'connectionType' => $request->input('connectionType', 100),
            // 运营商类型[0:UNKNOWN 1:CHINA_MOBILE 2:CHINA_TELECOM 3:CHINA_UNICOM 99:OTHER_OPERATOR]
            'operatorType'   => $request->input('operatorType', 0),
            // 需要的数据条数，最大 20 条
            'pageSize'       => config('app.info_count'),
            // 'pageSize'       => 1,
            // 分页页码
            'pageIndex'      => $pageIndex,
            // 需要的广告条数,最大 10 条
            'adCount'        => config('app.ad_count'),
            // 分类 id
            'catId'          => $catId
        ];
        $url = config('app.xd_config.url');
        $jsonStr = json_encode($post);

        $rep = $this->sendJsonPost($url, $jsonStr);
        $rep = json_decode($rep);
        if ($rep->code != 0) {
            $response->json(['code' => $rep->code, 'msg' => $rep->msg]);
        }

        $ad1 = $this->getSspJsAds($catId, $request);

        $data = $this->dealXdReturn($rep, $catId, $pageIndex);
        unset($rep);
        $data = array_merge($data, $ad1);
        $data = $this->fillWithAds($data);

        $total = count($data);

        $this->recordRequestLog(array_merge($post, [
            'country'  => $country,
            'province' => $province,
            'city'     => $city,
            'county'   => $county,
            'browser'  => $browser,
            'media_id' => $request->input('media_id'),
            'req_type' => 2
        ]));

        $response->json(['code' => 200, 'msg' => '返回成功', 'total' => $total, 'data' => $data]);
    }
     */

    /**
     * 获取xd信息流[给H5页面调用]
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response

    public function getH5XdInfo(Request $request, Response $response)
    {
        $pageIndex = intval($request->input('pageIndex', 1));
        $catId = intval($request->input('catId', 1001));
        $userAgent = $request->server('HTTP_USER_AGENT');
        $uaExtra = get_mobile_ua_extra($userAgent);
        $deviceType = $uaExtra['deviceType'];
        $osType = $uaExtra['osType'];
        $osVersion = $uaExtra['osVersion'];
        $fakeids = $this->getFakeIds($osType);
        $idfa = $fakeids->f_idfa;
        $imei = $fakeids->f_imei;
        $mac = getFakeMacAddress();
        $androidId = $fakeids->f_androidid;
        $model = '';
        $vendor = $uaExtra['brand'];
        $brand = $uaExtra['brand'];
        $screenWidth = $request->input('screenWidth', 640);
        $screenHeight = $request->input('screenHeight', 1136);
        $connectionType = $request->input('connectionType', 0);
        $operatorType = $request->input('operatorType', 0);

        // h5需要提供参数 catId, pageIndex
        $post = [
            // 使用协议版本,目前 1.0
            'version'        => '1.0',
            // 签名,目前不支持
            'sign'           => '',
            // 渠道 ID，请联系商务提供
            'channelId'      => config('app.xd_config.channelId'),
            // 应用 ID，请联系商务提供
            'appId'          => config('app.xd_config.appId'),
            // 广告位 id，请联系商务提供
            'slotId'         => config('app.xd_config.slotId'),
            // 设备类型[0:未知 UNKNOWN 1:手机 PHONE 2:平板 TABLET]
            'deviceType'     => $uaExtra['deviceType'],
            // 系统类型 [UNKNOWN = 0; //未知 ANDROID = 1; IOS = 2; WP = 3;]
            'osType'         => $osType,
            // 系统版本号,格式:a.b.c
            'osVersion'      => $uaExtra['osVersion'],
            // ios 设备必填
            'idfa'           => $fakeids->f_idfa,
            // IMEI 号(重要,必传)
            'imei'           => $fakeids->f_imei,
            // IMSI 号(重要,必传)
            'imsi'           => getFakeImsi(),
            // MAC 地址（随机伪造）
            'mac'            => getFakeMacAddress(),
            // Android ID 手机唯一标识
            'androidId'      => $fakeids->f_androidid,
            // 设备型号
            'model'          => $request->input('model', ''),
            // 厂商名称
            'vendor'         => $uaExtra['brand'],
            // 屏幕宽(px)
            'screenWidth'    => $request->input('screenWidth', 640),
            // 屏幕高(px)
            'screenHeight'   => $request->input('screenHeight', 1136),
            // 设备品牌 如 OPPO
            'brand'          => $uaExtra['brand'],
            // 浏览器 UA
            'userAgent'      => $userAgent,
            // 客户端 IPv4 地址(重要,必传)
            'ip'             => get_client_ip(),
            // 网络类型[0:UNKNOWN 1:CELL_UNKNOWN 2:2G 3:3G 4:4G 5:5G 100:WIFI 101:ETHERNET 999:NEW_TYPE]
            'connectionType' => $request->input('connectionType', 0),
            // 运营商类型[0:UNKNOWN 1:CHINA_MOBILE 2:CHINA_TELECOM 3:CHINA_UNICOM 99:OTHER_OPERATOR]
            'operatorType'   => $request->input('operatorType', 0),
            // 需要的数据条数，最大 20 条
            'pageSize'       => config('app.info_count'),
            // 'pageSize'       => 1,
            // 分页页码
            'pageIndex'      => $pageIndex,
            // 需要的广告条数,最大 10 条
            'adCount'        => config('app.ad_count'),
            // 分类 id
            'catId'          => $catId
        ];
        $url = config('app.xd_config.url');
        $jsonStr = json_encode($post);

        $rep = $this->sendJsonPost($url, $jsonStr);

        // 指定允许其他域名访问
        header('Access-Control-Allow-Origin:*');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        $rep = json_decode($rep);
        if ($rep->code != 0) {
            $response->json(['code' => $rep->code, 'msg' => $rep->msg]);
        }

        // $cpaAds = $this->getCpaAds($catId);
        // if (401 == $cpaAds) {
            // $response->json(['code'=> 401, 'msg'=>'CPA AD 返回失败']);
        // }

        $ad1 = $this->getSspJsAds($catId, $request);

        $data = $this->dealXdReturn($rep, $catId, $pageIndex);
        unset($rep);
        $data = array_merge($data, $ad1);
        // $data = array_merge($data, $cpaAds);
        $data = $this->fillWithAds($data);

        $total = count($data);

        $this->recordRequestLog(array_merge($post, [
            'media_id' => $request->input('media_id'),
            'req_type' => 2
        ]));


        $response->json(['code' => 200, 'msg' => '返回成功', 'total' => $total, 'data' => $data]);
    }
     */
    /**
     * 获取SSP 的 js 广告[H5对接]
     * @param int $catId
     * @param Request $request
     * @return array
     */
    private function getSspJsAds($catId = 1001, Request $request)
    {
        $post = [
            'sign'      => $request->input('sspsign'),
            'apptoken'  => $request->input('ssptoken'),
            'industrys' => $catId
        ];
        $tmp = $this->sendJsonPost(config('app.ssp_ad_js.url'), json_encode($post));
        return $this->dealSspJsAds($tmp);
    }

    /**
     * 处理ssp JS广告的返回
     *
     * @param string $ad1 返回结果
     *
     * @return array
     */
    private function dealSspJsAds($ad1)
    {
        $data = json_decode($ad1, true)['data'];
        $ads = [];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $ads[] = ['type' => 'ad', 'js' => $value['js']];
            }
        }
        return $ads;
    }

    /**
     * 获取SSP 的api 广告[app对接]
     * @param array $param
     * @return array
     */
    private function getSspApiAds($param)
    {
        $post = [
            'sign' => $param['sspsign'],
            'apptoken' => $param['ssptoken'],
            'industrys' => $param['catId'],
            'type' => $param['adType'],
            // 1=小图 2=大图
            'imgtype' => empty($param['imgType']) ? 1 : $param['imgType'],

            'data' => [
                'app' => [
                    'app_version' => [
                        'major' => '1.0.0',
                        'minor' => '',
                        'micro' => '',
                    ],
                ],
                'device' => [
                    'device_type' => $param['deviceType'],
                    'os_type' => $param['osType'],
                    'os_version' => [
                        'major' => $param['osVersion'],
                        'minor' => '',
                        'micro' => '',
                    ],
                    'vendor' => $param['vendor'],
                    'model' => $param['model'],
                    'screen_size' => [
                        'width' => $param['screenWidth'],
                        'height' => $param['screenHeight'],
                    ],
                    'udid' => [
                        'idfa' => $param['idfa'],
                        'imei' => $param['imei'],
                        'android_id' => $param['androidId'],
                        'mac' => $param['mac'],
                    ],
                ],
                'network' => [
                    'ipv4' => $param['ip'],
                    'connection_type' => $param['connectionType'],
                    'operator_type' => $param['operatorType'],
                ],
            ],
        ];
        //$tmp = $this->sendJsonPost(config('app.ssp_ad_api.url'), json_encode($post));
        $tmp = $this->sendJsonPost($param['apiUrl'], json_encode($post));
        return $this->dealSspApiAds($tmp);
    }

    /**
     * 处理ssp API 广告的返回
     *
     * @param string $ad 返回结果
     *
     * @return array
     */
    private function dealSspApiAds($ad)
    {
        $ad = json_decode($ad, true);
        $code = $ad['error_code'];
        $ad = $ad['wxad'];

        $ad['images'] =  isset($ad['image_src']) ? $ad['image_src'] : [];
        $ad['info_type'] = count($ad['images']);
        unset($ad['image_src'],$ad['icon_src']);
        $ad['type'] = 'ad';
        $ad['error_code'] = $code;
        return $ad;
    }

    /**
     * 处理xd信息流的返回
     *
     * @param array   $rep        请求响应结果
     * @param int     $catId      行业id
     * @param int     $pageIndex  分页页码
     *
     * @return array

    private function dealXdReturn($rep, $catId = 1001, $pageIndex = 1)
    {
        $data = [];
        foreach ($rep->cus as $cu) {
            $CuData = $cu->cuData;
            // $condition = ['f_id' => $CuData->id];
            $fill = [
                'id'             => $CuData->id,
                'title'          => $CuData->title,
                'images'         => $CuData->images,
                'is_top'         => $CuData->isTop,
                'recommend'      => $CuData->recommend,
                'detail_url'     => $CuData->detailUrl,
                'source'         => $CuData->source,
                'update_time'    => $CuData->updateTime,
                'info_type'      => count($CuData->images),
                'cat_id'         => $catId,
                'cat_name'       => config('app.info_cat.c' . $catId),
                'author_id'      => 0,
                'comment_counts' => $cu->commentCounts,
                'type'           => $cu->type
            ];
            $data[] = $fill;
        }
        return $data;
    } */

    /**
     * 数组中两个key 交换
     * @param array $array
     * @param int $i
     * @param int $j
     */
    public function arraySwap(&$array, $i, $j)
    {
        $temp = $array[$i];
        $array[$i] = $array[$j];
        $array[$j] = $temp;
    }

    public function fillWithAds($data)
    {
        if (isset($data[2 - 1]) && isset($data[9 - 1])) {
            $this->arraySwap($data, 2 - 1, 9 - 1);
        }
        if (isset($data[7 - 1]) && isset($data[10 - 1])) {
            $this->arraySwap($data, 7 - 1, 10 - 1);
        }
        return $data;
    }

    /**
     * 获取伪造的 idfa, androidid, imei
     * @param int $os
     * @return object
     */
    private function getFakeIds($os = 1)
    {
        if ($os == 3) {
            $os = 1;
        }
        $id = mt_rand(1, 1000000);
        $ret = (new DB)->table('fakeids')->where('f_ostype', $os)
                ->where('f_id', '>', $id)->orderBy('f_id', 'asc')->first();
        if ($os == 1) {
            $ret->f_idfa = '';
        } else {
            $ret->f_androidid = '';
        }
        return $ret;
    }

    /**
     * 记录请求的日志
     * @param array $post
     *
     * return void
     */
    public function recordRequestLog($post)
    {
        $log = [];
        foreach ($post as $key => $val) {
            if (in_array($key, ['version', 'sign', 'channelId', 'appId', 'slotId'])) {
                continue;
            }
            $log['f_' . snake_case($key)] = $val;
        }
        $log['f_date']    = date('Y-m-d');
        $log['f_addtime'] = getCurrentTime();

        Model::setDbName(DBENGINE_NAME);
        Model::saveData('info_stream_log_' . date('Ymd'), $log);
    }

    /**
     * android app 版本检查
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function appversionCheck(Request $request, Response $response)
    {
        $version = $request->input('vercode', '0.0.1');
        $appversion = (new DB)->table('appversions')->where('f_isdel', 0)
                   ->where('f_version', '>', $version)
                   ->orderBy('f_id', 'desc')->first();
        $data = ['need_update' => 0];
        if ($appversion) {
            $msg = '需要升级';
            $data['need_update'] = 1;
            $data['downloadurl'] = $appversion->f_downloadurl;
            $data['introduce']   = $appversion->f_intro;
            $data['version']     = $appversion->f_version;
        } else {
            $msg = '无需升级';
        }

        $response->json(['code' => 200, 'msg' => $msg, 'data' => $data]);
    }


    /**
     * 获取cpa广告
     * @param  integer $catid 分类id
     * @return mixed
     */
    public function getCpaAds($catid = 1016)
    {
        $data = ['catid' => $catid];
        $url = config('app.cpa_ad.url');
        $data['_url'] = config('app.cpa_ad._url');
        ksort($data);

        $token = md5(urldecode(http_build_query($data)).date('Y-m-d').config('app.cpa_ad.apikey'));


        $data['token'] = $token;
        $post = [
            'catid' => $catid,
            'token' => $token
        ];


        $cpaAds = $this->sendJsonPost($url, http_build_query($post), ["content-type: application/x-www-form-urlencoded"]);
        //print_r($cpaAds);
        $cpaAds = json_decode($cpaAds, true);

        if ($cpaAds['status']!=200) {
            return 401;
        }

        $cpaUrlPrefix = $cpaAds['urlAddess'];
        $cpaAds = $cpaAds['data'];
        foreach ($cpaAds as $key => $cad) {
            $cpaAds[$key]['type'] = 'ad';
            $cpaAds[$key]['title'] = $cad['f_campaign_name'] ? $cad['f_campaign_name'] : '';
            $cpaAds[$key]['strLinkUrl'] = $cad['f_short_url'];
            if ($cad['f_resource_url']) {
                $cpaAds[$key]['images'] = [$cpaUrlPrefix . '/' .  $cad['f_resource_url']];
            } else {
                $cpaAds[$key]['images'] = [];
            }
            $cpaAds[$key]['info_type'] = 1;
        }

        return $cpaAds;
    }

    /**
     * 获取 app 开屏广告[1:底部横幅 3:开屏 4：信息流]
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function getInitAd(Request $request, Response $response)
    {
        $osType = $request->input('osType', 1);
        $fakeids = $this->getFakeIds($osType);
        $param = [
            'sspsign' => $request->input('sspsign'),
            'ssptoken' => $request->input('ssptoken'),
            'catId' => intval($request->input('catId', 1001)),
            'adType' => intval($request->input('adType')),
            'deviceType' => $request->input('deviceType', 1),
            'osType' => $osType,
            'osVersion' => $request->input('osVersion', '0.0.0'),
            'vendor' => $request->input('vendor', ''),
            'model' => $request->input('model', ''),
            'screenWidth' => $request->input('screenWidth', 640),
            'screenHeight' => $request->input('screenHeight', 1136),
            'idfa' => $request->input('idfa') ? $request->input('idfa') : $fakeids->f_idfa,
            'imei' => $request->input('imei') ? $request->input('imei') : $fakeids->f_imei,
            'androidId' => $request->input('androidId') ? $request->input('androidId') : $fakeids->f_androidid,
            'mac' => $request->input('mac') ? $request->input('mac') : getFakeMacAddress(),
            'ip' => $request->input('ip') ? $request->input('ip') : get_client_ip(),
            'connectionType' => $request->input('connectionType', 0),
            'operatorType' => $request->input('operatorType', 0),
        ];
        if ($param['adType'] == 1) {
            $param['imgType'] = 1;
        } elseif ($param['adType'] == 3) {
            $param['imgType'] = 2;
        }
        $param['apiUrl'] = config('app.ssp_ad_api.url');
        $ad = $this->getSspApiAds($param);

        $response->json(['code' => 200, 'msg' => 'success', 'data' => [$ad]]);

    }

    /**
     * 获取百度信息流[给内部APP调用]
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function getBaiduFeeds(Request $request, Response $response)
    {
        $st = microtime(true);
        $media_id = $request->input('media_id');
        $pageIndex = intval($request->input('pageIndex', 1));
        $catId = $request->input('catId', 1001);
        $catId = $this->calculateCatId($catId);
        $deviceType = intval($request->input('deviceType', 1));
        $osType = intval($request->input('osType', 1));
        $osVersion = intval($request->input('osVersion'));
        $vendor = $request->input('vendor', '');
        $model = $request->input('model', '');
        $screenWidth = $request->input('screenWidth');
        $screenHeight = $request->input('screenHeight');
        $fakeids = $this->getFakeIds($osType);
        $idfa = $request->input('idfa') ? $request->input('idfa') : $fakeids->f_idfa;
        $imei = $request->input('imei') ? $request->input('imei') : $fakeids->f_imei;
        $mac  = $request->input('mac') ? $request->input('mac') : getFakeMacAddress();
        $androidId = $request->input('androidId') ? $request->input('androidId') : $fakeids->f_androidid;
        $ip = $request->input('ip') ? $request->input('ip') : get_client_ip();
        $connectionType = intval($request->input('connectionType'));
        $operatorType = intval($request->input('operatorType'));

        $post = $this->getBuildPost($pageIndex, $catId, $deviceType, $osType, $osVersion,
            $vendor, $model, $screenWidth, $screenHeight, $idfa, $imei, $mac, $androidId,
            $ip, $connectionType, $operatorType);

        $catId = $request->input('catId');
        $postx = compact('catId','deviceType','osType','osVersion','vendor','model',
            'screenWidth','screenHeight','idfa','imei','androidId',
            'mac','ip','connectionType','operatorType');

        $rep = $this->sendJsonPost(config('app.baidu_feeds.url'), json_encode($post));

        $mt = microtime(true);
        if ($rep !== false) {
            $rep = json_decode($rep);
            if ($rep->baseResponse->code != 200) {
                $response->json(['code' => $rep->baseResponse->code, 'msg' => $rep->baseResponse->msg, 'reason' => 'baidu error']);
            }
            $data = $this->dealBaiduReturn($rep->items);
        } else {
            $data = [];
        }
        unset($rep);

        $baidumt = $mt - $st ;

        $ssptoken = $request->input('ssptoken');
        $sspsign = $request->input('sspsign');

        $param = array_merge($postx, [
            'ssptoken'=> $ssptoken,
            'sspsign' => $sspsign,
            'adType' => 4
        ]);

        $sspApiAds = [];
        // 1026 猎奇
        // 1034 表演
        // 1036 音乐
        // 1037 影视周边
        // 1039 相声小品
        // 1040 舞蹈
        // 1041 安全出行
        // 1042 大自然
        if (in_array($catId, [1026, 1034, 1036,1037,1039,1040,1041,1042])) {
            $param['imgType'] = 2; // 大图
        } else {
            $param['imgType'] = 1; // 小图
        }
        $param['apiUrl'] = config('app.ssp_ad_api.url');

        $ad1 = $this->getSspApiAds($param);

        $ad1mt = microtime(true);

        $ad1ut = $ad1mt - $mt;
        if (0 < count($ad1['images'])) {
            array_push($sspApiAds, $ad1);
        }

        $param['imgType'] = 2;
        $ad2 = $this->getSspApiAds($param);

        $ad2mt = microtime(true);
        $ad2ut = $ad2mt - $ad1mt;
        if (0 < count($ad2['images'])) {
            array_push($sspApiAds, $ad2);
        }

        $data = array_merge($data, $sspApiAds);
        $data = $this->fillWithAds($data);

        $total = count($data);

        $this->recordRequestLog(array_merge($postx, [
            'media_id'=> $media_id,
            'company_id' => $request->input('company_id'),
            'req_type' => self::REQ_TYPE_API,
            'page_index' => $pageIndex
        ]));

        $response->json(['code' => 200, 'msg' => 'success', 'total' => $total, 'data' => $data,
            'ut' => [
                'baiduut' => $baidumt * 1000,
                'ad1ut' => $ad1ut * 1000,
                'ad2ut' => $ad2ut * 1000,
            ]
        ]);
    }

    /**
     * 获取百度信息流[给外部APP调用]
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function getBaiduFeedsExternal(Request $request, Response $response)
    {
        $data = $request->input("data");
        if (empty($data)) {
            $response->json(['code'=> 104, 'msg'=>'业务参数data缺失']);
        }

        $media_id = $request->input('media_id');

        $pageIndex = intval($data['page_index']);
        $catId = $data['catid'];
        $catId = $this->calculateCatId($catId);
        $deviceType = intval($data['device_type']);
        $osType = intval($data['os_type']);
        $osVersion = intval($data['os_version']);
        $vendor = $data['vendor'];  // 必填
        $model = $data['model'];    // 必填
        $screenWidth = $data['screen_width'];
        $screenHeight = $data['screen_height'];
        $fakeids = $this->getFakeIds($osType);
        $idfa = $data['idfa'] ? $data['idfa'] : $fakeids->f_idfa;
        $imei = $data['imei'] ? $data['imei'] : $fakeids->f_imei;
        $mac  = $data['mac'] ? $data['mac'] : getFakeMacAddress();
        $androidId = $data['android_id'] ? $data['android_id'] : $fakeids->f_androidid;
        $ip = $data['ip'] ? $data['ip'] : get_client_ip();
        $connectionType = intval($data['connection_type']);
        $operatorType = intval($data['operator_type']);

        $post = $this->getBuildPost($pageIndex, $catId, $deviceType, $osType, $osVersion,
            $vendor, $model, $screenWidth, $screenHeight, $idfa, $imei, $mac, $androidId,
            $ip, $connectionType, $operatorType);

        $postx = compact('catId','deviceType','osType','osVersion','vendor','model',
            'screenWidth','screenHeight','idfa','imei','androidId',
            'mac','ip','connectionType','operatorType');

        $rep = $this->sendJsonPost(config('app.baidu_feeds.url'), json_encode($post));
        $rep = json_decode($rep);
        if ($rep->baseResponse->code != 200) {
            $response->json(['code' => $rep->baseResponse->code, 'msg' => $rep->baseResponse->msg]);
        }

        $data = $this->dealBaiduReturn($rep->items);
        unset($rep);

        $ssptoken = $request->input('ssptoken');
        $sspsign = $request->input('sspsign');

        $param = array_merge($postx, [
            'ssptoken'=> $ssptoken,
            'sspsign' => $sspsign,
            'adType' => 4
        ]);

        $sspApiAds = [];
        if (in_array($catId, [1026, 1034, 1036,1037,1039,1040,1041,1042])) {
            $param['imgType'] = 2; // 大图
        } else {
            $param['imgType'] = 1; // 小图
        }
        $param['apiUrl'] = config('app.ssp_ad_api.url');
        $ad1 = $this->getSspApiAds($param);
        if (0 == $ad1['error_code']) {
            array_push($sspApiAds, $ad1);
        }

        $param['imgType'] = 2; // 大图
        $ad2 = $this->getSspApiAds($param);
        if (0 == $ad2['error_code']) {
            array_push($sspApiAds, $ad2);
        }

        $data = array_merge($data, $sspApiAds);
        $data = $this->fillWithAds($data);

        $total = count($data);

        $this->recordRequestLog(array_merge($postx, [
            'media_id'=> $media_id,
            'company_id' => $request->input('company_id'),
            'req_type' => self::REQ_TYPE_API,
            'page_index' => $pageIndex
        ]));

        $response->json(['code' => 200, 'msg' => 'success', 'total' => $total, 'data' => $data]);
    }

    /**
     * 处理百度信息流的返回
     *
     * @param array   $rep        请求响应结果
     *
     * @return array
     */
    private function dealBaiduReturn($rep)
    {
        $data = [];

        foreach ($rep as $cu) {
            $CuData = json_decode($cu->data);
            $images = empty($CuData->images) ? [] : $CuData->images;
            if (!empty($CuData->thumbUrl)) {
                if (strpos($CuData->thumbUrl, 'http:') === false) {
                    $CuData->thumbUrl = 'http:' . $CuData->thumbUrl;
                }
                $images = [$CuData->thumbUrl];
            }
            if (!empty($CuData->smallImageList)) {
                $images = [];
                foreach ($CuData->smallImageList as $val) {
                    if (strpos($val->imageUrl, 'http:') === false) {
                        $val->imageUrl = 'http:' . $val->imageUrl;
                    }
                    $images[] = $val->imageUrl;
                }
            }
            $isTop = empty($CuData->isTop) ? 0 : $CuData->isTop;
            $recommend = empty($CuData->recommend) ? 0 : $CuData->recommend;
            $source = is_a($CuData->source, 'stdClass') ? $CuData->source->name : $CuData->source;
            $updateTime = empty($CuData->updateTime) ? getCurrentTime() : $CuData->updateTime;
            $fill = [
                'id'             => $CuData->id,
                'title'          => $CuData->title,
                'is_top'         => $isTop,
                'recommend'      => $recommend,
                'detail_url'     => $CuData->detailUrl,
                'source'         => $source,
                'update_time'    => $updateTime,
                'info_type'      => count($images),
                'cat_id'         => $CuData->catInfo->id,
                'cat_name'       => $CuData->catInfo->name,
                'author_id'      => 0,
                'comment_counts' => $cu->commentCounts,
                'type'           => $cu->type
            ];
            if ($images) {
                $fill['images'] = $images;
            }
            $data[] = $fill;
        }
        return $data;
    }

    public function getBuildPost($pageIndex, $catId, $deviceType, $osType, $osVersion,
        $vendor, $model, $screenWidth, $screenHeight, $idfa, $imei, $mac, $androidId,
        $ip, $connectionType, $operatorType)
    {
        $timestamp = time();
        if (1 == $osType) {
            $appsid = config('app.baidu_feeds.appsid.android');
        } else {
            $appsid = config('app.baidu_feeds.appsid.ios');
        }
        $token = config('app.baidu_feeds.token');
        if ($catId == 1003) {
            $contentType = 1;
        } elseif (in_array($catId, [1026, 1034, 1036,1037,1039,1040,1041,1042])) {
            $contentType = 2;
        } else {
            $contentType = 0;
        }
        $data = [
            'contentParams' => [
                'adCount' => config('app.ad_count'),
                'pageSize' => config('app.info_count'),
                'pageIndex' => $pageIndex,
                // 0:新闻，1:图片，2:视频
                'contentType' => $contentType,
                'catIds' => explode(',', $catId),
                // 列表中的某条标题中显示图片的数量, 空表示不限制，如3表示题图中显示三张图片
                'minPicCount' => '',
                // 场景,0表示默认，3表示最热点击，6表示本地渠道
                'listScene' => 0,
            ],
            'device' => [
                'deviceType'   => $deviceType,
                'osType'       => $osType,
                'osVersion'    => $osVersion,
                'vendor'       => $vendor,
                'model'        => $model,
                'screenSize'   => [
                    'width'    => $screenWidth,
                    'height'   => $screenHeight
                ],
                'udid'         => [
                    'idfa'     => $idfa,
                    'imei'     => $imei,
                    'mac'      => $mac,
                    'imeiMd5'  => md5($imei),
                    'androidId'=> $androidId,
                ]
            ],
            'network' => [
                // 必填.Ipv4，格式需要为”xxx.xxx.xxx.xxx”
                'ipv4' => $ip,
                // 必填网络连接类型，用于判断网速 CONNECTION_UNKNOWN = 0无法探测当前网络状态 CELL_UNKNOWN =
                // 1蜂窝数据接入，未知网络类型CELL_2G = 2蜂窝数据2G网络 CELL_3G = 3 蜂窝数据3G网络 CELL_4G = 4 蜂窝数据4G网
                // CELL_5G =5蜂窝数据5G网络 WIFI = 100Wi-Fi网络接入 ETHERNET = 101以太网接入 NEW_TYPE = 999未知新类型[int类型]
                'connectionType' => $connectionType,
                // 必填.移动运营商类型，用于运营商定向广告UNKNOWN_OPERATOR = 0未知的运营商, CHINA_MOBILE = 1
                // 中国移动,CHINA_TELECOM = 2 中国电信,CHINA_UNICOM = 3 中国联通, OTHER_OPERATOR = 99 其他运营商[int类型]
                'operatorType' => $operatorType,
            ],
        ];
        $signature = md5($timestamp . $token . json_encode($data));
        $post = [
            'appsid' => $appsid,
            'timestamp' => $timestamp,
            'data' => $data,
            'token' => $token,
            'signature' => $signature,
        ];
        return $post;
    }

    /**
     * 获取百度信息流+SSP js广告[给外部APP调用]
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function getBaiduFeedsJsExternal(Request $request, Response $response)
    {
        $media_id = $request->input('media_id');
        $data     = $request->input("data");
        if (empty($data)) {
            $response->json(['code'=> 104, 'msg'=>'业务参数data缺失']);
        }
        // 新网程
        if ($media_id == 5) {
            //检查必填参数
            $required = [
                'catid','page_index','device_type','os_type','country',
                'province','city','county','ip','useragent',
                'browser','screen_width','screen_height','mac','os_version'
            ];
            foreach($required as $req) {
                if (isset($data[$req])) {
                    continue;
                } else {
                    $response->json(['code'=> 104, 'msg'=>'业务参数data缺失,请检查 ' . $req . ' 参数。']);
                }
            }
        }

        // 盛天wifi
        if ($media_id == 7) {
            //检查必填参数
            $required = [
                'catid','page_index','device_type','os_type','mac','vendor','brand','ip',
                'useragent','screen_width','screen_height','connection_type'
            ];
            foreach($required as $req) {
                if (!empty($data[$req])) {
                    continue;
                } else {
                    $response->json(['code'=> 104, 'msg'=>'业务参数data缺失,请检查 ' . $req . ' 参数。']);
                }
            }
        }

        // Hi电扫码充电
        if ($media_id == 9) {
            //检查必填参数
            $required = [
                'catid','page_index','device_type','os_type','os_version','ip',
                'useragent','screen_width','screen_height','connection_type'
            ];
            foreach($required as $req) {
                if (!empty($data[$req])) {
                    continue;
                } else {
                    $response->json(['code'=> 104, 'msg'=>'业务参数data缺失,请检查 ' . $req . ' 参数。']);
                }
            }
        }

        $pageIndex    = empty($data['page_index']) ? 1 : $data['page_index'];
        $catId        = empty($data['catid']) ? 1001 : $data['catid'];
        $catId        = $this->calculateCatId($catId);
        $userAgent    = empty($data['useragent']) ? '' : $data['useragent'];
        $deviceType   = empty($data['device_type']) ? '' : $data['device_type'];
        $osType       = empty($data['os_type']) ? '' : $data['os_type'];
        $osVersion    = empty($data['os_version']) ? '' : $data['os_version'];
        $vendor       = empty($data['vendor']) ? '' : $data['vendor'];
        $model        = empty($data['model']) ? '' : $data['model'];
        $brand        = empty($data['brand']) ? '' : $data['brand'];
        $screenWidth  = empty($data['screen_width']) ? 640 : $data['screen_width'];
        $screenHeight = empty($data['screen_height']) ? 1136 : $data['screen_height'];
        $fakeids      = $this->getFakeIds($osType);
        $idfa         = empty($data['idfa']) ? $fakeids->f_idfa : $data['idfa'];
        $imei         = empty($data['imei']) ? $fakeids->f_imei : $data['imei'];
        $mac          = empty($data['mac']) ? getFakeMacAddress() : $data['mac'];
        $androidId    = empty($data['androidid']) ? $fakeids->f_androidid : $data['androidid'];
        if (!empty($data['ip']) && $data['ip']) {
            $ip = $data['ip'];
            $filter = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
            if (!filter_var($ip, FILTER_VALIDATE_IP, $filter)) {
                $response->json(['code'=> 104, 'msg'=>'业务参数data 中的 ip 不正确，必须是公网ip。']);
            }
        } else {
            $ip = get_client_ip();
        }
        $connectionType = empty($data['connection_type']) ? 0 : $data['connection_type'];
        $operatorType   = empty($data['operator_type']) ? 0 : $data['operator_type'];
        $country        = empty($data['country']) ? '' : $data['country'];
        $province       = empty($data['province']) ? '' : $data['province'];
        $city           = empty($data['city']) ? '' : $data['city'];
        $county         = empty($data['county']) ? '' : $data['county'];
        $browser        = empty($data['browser']) ? '' : $data['browser'];

        $post  = $this->getBuildPost($pageIndex, $catId, $deviceType, $osType, $osVersion,
            $vendor, $model, $screenWidth, $screenHeight, $idfa, $imei, $mac, $androidId,
            $ip, $connectionType, $operatorType);

        $rep = $this->sendJsonPost(config('app.baidu_feeds.url'), json_encode($post));
        $rep = json_decode($rep);
        if ($rep->baseResponse->code != 200) {
            $response->json(['code' => $rep->baseResponse->code, 'msg' => $rep->baseResponse->msg]);
        }
        $data = $this->dealBaiduReturn($rep->items);
        unset($rep);

        $ad = $this->getSspJsAds($catId, $request);

        $data = array_merge($data, $ad);
        $data = $this->fillWithAds($data);

        $total = count($data);

        $log = [
            'deviceType'     => $deviceType,
            'osType'         => $osType,
            'osVersion'      => $osVersion,
            'idfa'           => $fakeids->f_idfa,
            'imei'           => $fakeids->f_imei,
            'imsi'           => getFakeImsi(),
            'mac'            => $mac,
            'androidId'      => $fakeids->f_androidid,
            'model'          => $request->input('model', ''),
            'vendor'         => $vendor,
            'screenWidth'    => $screenWidth ? $screenWidth : 640,
            'screenHeight'   => $screenHeight ? $screenHeight : 1136,
            'brand'          => $brand,
            'userAgent'      => $userAgent,
            'ip'             => $ip,
            'connectionType' => $connectionType,
            'operatorType'   => $operatorType,
            'pageSize'       => config('app.info_count'),
            'pageIndex'      => $pageIndex,
            'adCount'        => config('app.ad_count'),
            'catId'          => $catId,
            'country'        => $country,
            'province'       => $province,
            'city'           => $city,
            'county'         => $county,
            'browser'        => $browser,
            'media_id'       => $media_id,
            'company_id'     => $request->input('company_id'),
            'req_type'       => self::REQ_TYPE_JS
        ];
        $this->recordRequestLog($log);

        $response->json(['code' => 200, 'msg' => 'success', 'total' => $total, 'data' => $data]);
    }

    /**
     * 获取百度信息流+SSP js广告[给内部H5调用]
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function getBaiduFeedsJs(Request $request, Response $response)
    {
        $media_id = $request->input('media_id');
        $pageIndex = intval($request->input('pageIndex', 1));

        $catId = $request->input('catId', 1001);
        $catId = $this->calculateCatId($catId);
        $userAgent = $request->server('HTTP_USER_AGENT');
        $uaExtra = get_mobile_ua_extra($userAgent);
        $deviceType = $uaExtra['deviceType'];
        $osType = $uaExtra['osType'];
        $osVersion = $uaExtra['osVersion'];
        //$osType = 2;
        //$fakeids = $this->getFakeIds($osType);
        //$idfa = $fakeids->f_idfa;
        //$imei = $fakeids->f_imei;
        //$mac = getFakeMacAddress();
        //$androidId = $fakeids->f_androidid;
        
        $osType = 1;
        //$idfa = '0336F36D-E2F1-44DB-9FE1-D688BFC68D1A';
        $idfa = '';
        $imei = '869141024495113';
        $mac = '48:db:50:a1:fd:68';
        $androidId = 'f600e88ad23eb7c1';
        
        // $imei = '867529062498238';
        // $mac = '6:A8:E1:78:28:FC';
        // $androidId = '162e96e851eba303';

        $model = '';
        $vendor = $uaExtra['brand'];
        $brand = $uaExtra['brand'];
        $screenWidth = $request->input('screenWidth', 640);
        $screenHeight = $request->input('screenHeight', 1136);
        $connectionType = $request->input('connectionType', 100);
        $operatorType = $request->input('operatorType', 99);
        if (ONLINE) {
            $ip = $request->input('ip', get_client_ip());
        } else {
            $ip = $request->input('ip', '101.95.172.30');
        }

        $post  = $this->getBuildPost($pageIndex, $catId, $deviceType, $osType, $osVersion,
            $vendor, $model, $screenWidth, $screenHeight, $idfa, $imei, $mac, $androidId,
            $ip, $connectionType, $operatorType);

        $rep = $this->sendJsonPost(config('app.baidu_feeds.url'), json_encode($post));
        $rep = json_decode($rep);

        if ($rep->baseResponse->code != 200) {
            $response->json(['code' => $rep->baseResponse->code, 'msg' => $rep->baseResponse->msg]);
        }
        $data = $this->dealBaiduReturn($rep->items);
        unset($rep);

        $ad = $this->getSspJsAds($catId, $request);

        $data = array_merge($data, $ad);
        $data = $this->fillWithAds($data);

        $total = count($data);

        $log = [
            'deviceType'     => $deviceType,
            'osType'         => $osType,
            'osVersion'      => $osVersion,
            'idfa'           => $idfa,
            'imei'           => $imei,
            'imsi'           => getFakeImsi(),
            'mac'            => $mac,
            'androidId'      => $androidId,
            'model'          => $request->input('model', ''),
            'vendor'         => $vendor,
            'screenWidth'    => $screenWidth,
            'screenHeight'   => $screenHeight,
            'brand'          => $brand,
            'userAgent'      => $userAgent,
            'ip'             => $ip,
            'connectionType' => $connectionType,
            'operatorType'   => $operatorType,
            'pageSize'       => config('app.info_count'),
            'pageIndex'      => $pageIndex,
            'adCount'        => config('app.ad_count'),
            'catId'          => $catId,
            'media_id'       => $media_id,
            'company_id'     => $request->input('company_id'),
            'req_type'       => self::REQ_TYPE_JS
        ];
        $this->recordRequestLog($log);

        $response->json(['code' => 200, 'msg' => 'success', 'total' => $total, 'data' => $data]);
    }

    /**
     * 记录趣悦头条启动时的地理位置信息[给内部APP调用]
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function recordGeoLog(Request $request, Response $response)
    {
        $log = [
            'f_ostype' => $request->input('osType'),
            'f_idfa' => $request->input('idfa'),
            'f_imei' => $request->input('imei'),
            'f_androidid' => $request->input('androidId'),
            'f_longitude' => $request->input('longitude'),
            'f_latitude' => $request->input('latitude'),
            'f_altitude' => $request->input('altitude'),
            'f_date' => date('Y-m-d')
        ];
        Model::saveData('geo_log', $log);
        $response->json(['code' => 200, 'msg' => 'success']);
    }

    /**
     * 记录趣阅头条点击详情页日志[给内部APP调用]
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function recordDetailLog(Request $request, Response $response)
    {
        $log = [
            'f_company_id' => $request->input('company_id'),
            'f_media_id' => $request->input('media_id'),
            'f_device_type' => $request->input('deviceType'),
            'f_os_type' => $request->input('osType'),
            'f_user_agent' => $request->input('userAgent'),
            'f_ip' => get_client_ip(),
            'f_cat_id' => $request->input('catId'),
            'f_bdlink' => $request->input('bdlink'),
            'f_country' => '',
            'f_province' => '',
            'f_city' => '',
            'f_county' => '',
            'f_gender' => 0,
            'f_unionid' => '',
            'f_date'    => getCurrentDate(),
            'f_addtime' => getCurrentTime(),
        ];
        Model::setDbName(DBENGINE_NAME);
        Model::saveData('info_view_log_' . date('Ymd'), $log);

        $user_id = $request->input('userid', 0);
        // 记录积分
        OpCredit::changeCredit($user_id, 3);
        $response->json(['code' => 200, 'msg' => 'success']);
    }

    /**
     * 测试调用
     */
    public function getBaiduFeedsJsTest(Request $request, Response $response)
    {
        $data = $request->input("data");
        if (empty($data)) {
            $response->json(['code'=> 104, 'msg'=>'业务参数data缺失']);
        }

        $media_id = $request->input('media_id');

        $pageIndex = intval($data['page_index']);
        $catId = $data['catid'];
        $catId = $this->calculateCatId($catId);
        $deviceType = intval($data['device_type']);
        $osType = intval($data['os_type']);
        $osVersion = intval($data['os_version']);
        $vendor = isset($data['vendor']) ? $data['vendor'] : '';  // 必填
        $model = isset($data['model']) ? $data['model'] : '';    // 必填
        $screenWidth = isset($data['screen_width']) ? $data['screen_width'] : '';
        $screenHeight = isset($data['screen_height']) ? $data['screen_height'] : '';
        $fakeids = $this->getFakeIds($osType);
        $idfa = isset($data['idfa']) ? $data['idfa'] : $fakeids->f_idfa;
        $imei = isset($data['imei']) ? $data['imei'] : $fakeids->f_imei;
        $mac  = isset($data['mac']) ? $data['mac'] : getFakeMacAddress();
        $androidId = isset($data['android_id']) ? $data['android_id'] : $fakeids->f_androidid;
        $ip = $data['ip'] ? $data['ip'] : get_client_ip();
        $connectionType = intval($data['connection_type']);
        $operatorType = isset($data['operator_type']) ?  intval($data['operator_type']) :1;

        $data = [];
        $data = json_decode('[{
                "id": "6328795103521997",
                "title": "还记得《宝贝计划》中成龙最爱的小baby吗？如今他越长越像成龙",
                "is_top": 0,
                "recommend": 0,
                "detail_url": "https://cpu.baidu.com/api/1022/ba3bef16/detail/6328795103521997/news?im=cbgJyhgD0OiiOPFECkw1h-U-qOaUNq-b_KuzbLvKbvfcMpCAvGgJ0Befujx4an5Ytuf9QuhjwxvU6Cs8I_Pc2igKWdyLpwfxUnkAKzCyn4IMFZGghYtKn1D9F6r71YTt2OY1Zc_uB9MctuEWTbRyDr3wUaM6CuSav7byKfUXRat5SSVlfvvSPQvbVXXEJOYRcLtogOf2D8kgsPfgEpHCtKDlqiYN_XNZ3KoE9OdYmb47Jow0zPIA9CnqTiSumwOo5BtC_R64VwKnQ9AWOwvqDBN-Cms-Qqu1mkePrS2UvE3jqasqmnxBsYZOAsjum_6Sm4sX8N0NQrMEe-MvSIOMGw&aid=XE4DeF2UNXgUB1fRgeA_AdfZENkB3D2ia-yKrnfml1fYtWIK_R1KA68QP8Obe9iOe7Z8CCyt_1nrQGdKMBBBSkgaZ6zSqF4SLyF_XRjoCfNOHuZn7P3zRzsdh0y796CBj8jwcl_mwyMhLLp62xvgR_DAXr7DD6y_9_wwEHObyUZaGjXVyKyFZPPJFMAuoa93kVO5BtTR6MZyuINbMHMttvars8ExCYHR6XWQLp2rGX41ZC2enY4b5BL0tiJLV99jdlTY6zK9z6taYLstNC9Qz3VkgiF75nccmbVwwgrgODOXID5ZomgEb5Ad5xcAr8e_wjcq8dQCqlzzvLQeWhjmpg&scene=0&logid=8180611633823215125381469921191794&no_list=1&foward=api&api_version=2",
                "source": "爱说娱乐全",
                "update_time": "2017-12-06 10:42:27",
                "info_type": 3,
                "cat_id": 1001,
                "cat_name": "娱乐",
                "author_id": 0,
                "comment_counts": 0,
                "type": "news",
                "images": [
                    "https://publish-pic-cpu.baidu.com/c0f4de38-770e-4f9f-ad9e-8b07b35f74f1.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/3a4b40fd-1eae-4de5-bb9f-b6d86a3051f3.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/d55bf3ec-39a8-4e9f-b59a-2c09b4f14738.jpeg@w_228,h_152"
                ]
            },
            {
                "type": "ad",
                "js": "<script src=\"http://sj.adunite.com/hidian1-51943-64499-1157.js\" id=\"hidian151943644991157\"></script>"
            },
            {
                "id": "6232266250415237",
                "title": "潘春春不愧是超越柳岩的女神, 连她的家宠物都看不下去了!",
                "is_top": 0,
                "recommend": 0,
                "detail_url": "https://cpu.baidu.com/api/1022/ba3bef16/detail/6232266250415237/news?im=cbgJyhgD0OiiOPFECkw1h-U-qOaUNq-b_KuzbLvKbvfcMpCAvGgJ0Befujx4an5Ytuf9QuhjwxvU6Cs8I_Pc2igKWdyLpwfxUnkAKzCyn4IMFZGghYtKn1D9F6r71YTt2OY1Zc_uB9MctuEWTbRyDr3wUaM6CuSav7byKfUXRat5SSVlfvvSPQvbVXXEJOYRcLtogOf2D8kgsPfgEpHCtKDlqiYN_XNZ3KoE9OdYmb47Jow0zPIA9CnqTiSumwOo5BtC_R64VwKnQ9AWOwvqDBN-Cms-Qqu1mkePrS2UvE3jqasqmnxBsYZOAsjum_6Sm4sX8N0NQrMEe-MvSIOMGw&aid=XE4DeF2UNXgUB1fRgeA_AdfZENkB3D2ia-yKrnfml1fYtWIK_R1KA68QP8Obe9iOe7Z8CCyt_1nrQGdKMBBBSkgaZ6zSqF4SLyF_XRjoCfNOHuZn7P3zRzsdh0y796CBj8jwcl_mwyMhLLp62xvgR_DAXr7DD6y_9_wwEHObyUZaGjXVyKyFZPPJFMAuoa93kVO5BtTR6MZyuINbMHMttvars8ExCYHR6XWQLp2rGX41ZC2enY4b5BL0tiJLV99jdlTY6zK9z6taYLstNC9Qz3VkgiF75nccmbVwwgrgODOXID5ZomgEb5Ad5xcAr8e_wjcq8dQCqlzzvLQeWhjmpg&scene=0&logid=8180611633823215125381469921191794&no_list=1&foward=api&api_version=2",
                "source": "东方头条",
                "update_time": "2017-12-04 10:21:15",
                "info_type": 3,
                "cat_id": 1001,
                "cat_name": "娱乐",
                "author_id": 0,
                "comment_counts": 0,
                "type": "news",
                "images": [
                    "https://publish-pic-cpu.baidu.com/797e26eb-0f41-4bac-a28c-11e85e797355.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/66c32232-10cc-4b0d-98f4-7d43a87e1a18.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/0a3b090b-9610-44b1-a05b-34c90cfd9777.jpeg@w_228,h_152"
                ]
            },
            {
                "id": "6272672094776483",
                "title": "她是刘德华和周星驰都迷恋的女人，沦落到捡垃圾，今54岁准备复出",
                "is_top": 0,
                "recommend": 0,
                "detail_url": "https://cpu.baidu.com/api/1022/ba3bef16/detail/6272672094776483/news?im=cbgJyhgD0OiiOPFECkw1h-U-qOaUNq-b_KuzbLvKbvfcMpCAvGgJ0Befujx4an5Ytuf9QuhjwxvU6Cs8I_Pc2igKWdyLpwfxUnkAKzCyn4IMFZGghYtKn1D9F6r71YTt2OY1Zc_uB9MctuEWTbRyDr3wUaM6CuSav7byKfUXRat5SSVlfvvSPQvbVXXEJOYRcLtogOf2D8kgsPfgEpHCtKDlqiYN_XNZ3KoE9OdYmb47Jow0zPIA9CnqTiSumwOo5BtC_R64VwKnQ9AWOwvqDBN-Cms-Qqu1mkePrS2UvE3jqasqmnxBsYZOAsjum_6Sm4sX8N0NQrMEe-MvSIOMGw&aid=XE4DeF2UNXgUB1fRgeA_AdfZENkB3D2ia-yKrnfml1fYtWIK_R1KA68QP8Obe9iOe7Z8CCyt_1nrQGdKMBBBSkgaZ6zSqF4SLyF_XRjoCfNOHuZn7P3zRzsdh0y796CBj8jwcl_mwyMhLLp62xvgR_DAXr7DD6y_9_wwEHObyUZaGjXVyKyFZPPJFMAuoa93kVO5BtTR6MZyuINbMHMttvars8ExCYHR6XWQLp2rGX41ZC2enY4b5BL0tiJLV99jdlTY6zK9z6taYLstNC9Qz3VkgiF75nccmbVwwgrgODOXID5ZomgEb5Ad5xcAr8e_wjcq8dQCqlzzvLQeWhjmpg&scene=0&logid=8180611633823215125381469921191794&no_list=1&foward=api&api_version=2",
                "source": "北青网",
                "update_time": "2017-12-05 08:08:21",
                "info_type": 3,
                "cat_id": 1001,
                "cat_name": "娱乐",
                "author_id": 0,
                "comment_counts": 0,
                "type": "news",
                "images": [
                    "https://publish-pic-cpu.baidu.com/a1fb7157-2f00-468c-a136-c2dbb28ee48b.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/15c06151-ceb0-4694-81c6-4c3fe64ec1f8.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/28683252-2db5-4425-a6a7-3ed80df4c644.jpeg@w_228,h_152"
                ]
            },
            {
                "id": "6235879391653001",
                "title": "世界各国公主大盘点，论长相迪拜公主完胜，她们的长相你给几分？",
                "is_top": 0,
                "recommend": 0,
                "detail_url": "https://cpu.baidu.com/api/1022/ba3bef16/detail/6235879391653001/news?im=cbgJyhgD0OiiOPFECkw1h-U-qOaUNq-b_KuzbLvKbvfcMpCAvGgJ0Befujx4an5Ytuf9QuhjwxvU6Cs8I_Pc2igKWdyLpwfxUnkAKzCyn4IMFZGghYtKn1D9F6r71YTt2OY1Zc_uB9MctuEWTbRyDr3wUaM6CuSav7byKfUXRat5SSVlfvvSPQvbVXXEJOYRcLtogOf2D8kgsPfgEpHCtKDlqiYN_XNZ3KoE9OdYmb47Jow0zPIA9CnqTiSumwOo5BtC_R64VwKnQ9AWOwvqDBN-Cms-Qqu1mkePrS2UvE3jqasqmnxBsYZOAsjum_6Sm4sX8N0NQrMEe-MvSIOMGw&aid=XE4DeF2UNXgUB1fRgeA_AdfZENkB3D2ia-yKrnfml1fYtWIK_R1KA68QP8Obe9iOe7Z8CCyt_1nrQGdKMBBBSkgaZ6zSqF4SLyF_XRjoCfNOHuZn7P3zRzsdh0y796CBj8jwcl_mwyMhLLp62xvgR_DAXr7DD6y_9_wwEHObyUZaGjXVyKyFZPPJFMAuoa93kVO5BtTR6MZyuINbMHMttvars8ExCYHR6XWQLp2rGX41ZC2enY4b5BL0tiJLV99jdlTY6zK9z6taYLstNC9Qz3VkgiF75nccmbVwwgrgODOXID5ZomgEb5Ad5xcAr8e_wjcq8dQCqlzzvLQeWhjmpg&scene=0&logid=8180611633823215125381469921191794&no_list=1&foward=api&api_version=2",
                "source": "虎妈萌娃",
                "update_time": "2017-12-04 12:10:17",
                "info_type": 3,
                "cat_id": 1001,
                "cat_name": "娱乐",
                "author_id": 0,
                "comment_counts": 0,
                "type": "news",
                "images": [
                    "https://publish-pic-cpu.baidu.com/a934c53e-84dc-4d50-affd-d3df1c4b62b0.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/7164d66f-8404-4070-b315-0ca66ac52246.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/0928856d-f984-42a8-8342-2aa85b13d9b4.jpeg@w_228,h_152"
                ]
            },
            {
                "id": "6232153775959182",
                "title": "47岁张嘉译坐飞机被偶遇，空姐镜头下的张嘉译长这样",
                "is_top": 0,
                "recommend": 0,
                "detail_url": "https://cpu.baidu.com/api/1022/ba3bef16/detail/6232153775959182/news?im=cbgJyhgD0OiiOPFECkw1h-U-qOaUNq-b_KuzbLvKbvfcMpCAvGgJ0Befujx4an5Ytuf9QuhjwxvU6Cs8I_Pc2igKWdyLpwfxUnkAKzCyn4IMFZGghYtKn1D9F6r71YTt2OY1Zc_uB9MctuEWTbRyDr3wUaM6CuSav7byKfUXRat5SSVlfvvSPQvbVXXEJOYRcLtogOf2D8kgsPfgEpHCtKDlqiYN_XNZ3KoE9OdYmb47Jow0zPIA9CnqTiSumwOo5BtC_R64VwKnQ9AWOwvqDBN-Cms-Qqu1mkePrS2UvE3jqasqmnxBsYZOAsjum_6Sm4sX8N0NQrMEe-MvSIOMGw&aid=XE4DeF2UNXgUB1fRgeA_AdfZENkB3D2ia-yKrnfml1fYtWIK_R1KA68QP8Obe9iOe7Z8CCyt_1nrQGdKMBBBSkgaZ6zSqF4SLyF_XRjoCfNOHuZn7P3zRzsdh0y796CBj8jwcl_mwyMhLLp62xvgR_DAXr7DD6y_9_wwEHObyUZaGjXVyKyFZPPJFMAuoa93kVO5BtTR6MZyuINbMHMttvars8ExCYHR6XWQLp2rGX41ZC2enY4b5BL0tiJLV99jdlTY6zK9z6taYLstNC9Qz3VkgiF75nccmbVwwgrgODOXID5ZomgEb5Ad5xcAr8e_wjcq8dQCqlzzvLQeWhjmpg&scene=0&logid=8180611633823215125381469921191794&no_list=1&foward=api&api_version=2",
                "source": "鱼乐小先生",
                "update_time": "2017-12-04 10:17:25",
                "info_type": 3,
                "cat_id": 1001,
                "cat_name": "娱乐",
                "author_id": 0,
                "comment_counts": 0,
                "type": "news",
                "images": [
                    "https://publish-pic-cpu.baidu.com/27d71f0c-711b-4646-bbac-2a2ecfac1b6b.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/9f79b8da-a809-4bdd-ba95-fe5c268362dd.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/a9251a97-a629-4d66-9ac8-6ada81e978c4.jpeg@w_228,h_152"
                ]
            },
            {
                "type": "ad",
                "js": "<script src=\"http://sj.adunite.com/-70863-46918-.js\" id=\"7086346918\"></script>"
            },
            {
                "id": "6258995442511023",
                "title": "宋喆反咬马蓉一口，紧接着母亲又被曝出丑事！马蓉急了怒怼媒体！",
                "is_top": 0,
                "recommend": 0,
                "detail_url": "https://cpu.baidu.com/api/1022/ba3bef16/detail/6258995442511023/news?im=cbgJyhgD0OiiOPFECkw1h-U-qOaUNq-b_KuzbLvKbvfcMpCAvGgJ0Befujx4an5Ytuf9QuhjwxvU6Cs8I_Pc2igKWdyLpwfxUnkAKzCyn4IMFZGghYtKn1D9F6r71YTt2OY1Zc_uB9MctuEWTbRyDr3wUaM6CuSav7byKfUXRat5SSVlfvvSPQvbVXXEJOYRcLtogOf2D8kgsPfgEpHCtKDlqiYN_XNZ3KoE9OdYmb47Jow0zPIA9CnqTiSumwOo5BtC_R64VwKnQ9AWOwvqDBN-Cms-Qqu1mkePrS2UvE3jqasqmnxBsYZOAsjum_6Sm4sX8N0NQrMEe-MvSIOMGw&aid=XE4DeF2UNXgUB1fRgeA_AdfZENkB3D2ia-yKrnfml1fYtWIK_R1KA68QP8Obe9iOe7Z8CCyt_1nrQGdKMBBBSkgaZ6zSqF4SLyF_XRjoCfNOHuZn7P3zRzsdh0y796CBj8jwcl_mwyMhLLp62xvgR_DAXr7DD6y_9_wwEHObyUZaGjXVyKyFZPPJFMAuoa93kVO5BtTR6MZyuINbMHMttvars8ExCYHR6XWQLp2rGX41ZC2enY4b5BL0tiJLV99jdlTY6zK9z6taYLstNC9Qz3VkgiF75nccmbVwwgrgODOXID5ZomgEb5Ad5xcAr8e_wjcq8dQCqlzzvLQeWhjmpg&scene=0&logid=8180611633823215125381469921191794&no_list=1&foward=api&api_version=2",
                "source": "娱乐营长",
                "update_time": "2017-12-05 00:05:18",
                "info_type": 3,
                "cat_id": 1001,
                "cat_name": "娱乐",
                "author_id": 0,
                "comment_counts": 0,
                "type": "news",
                "images": [
                    "https://publish-pic-cpu.baidu.com/8c76eb7b-41ff-4920-a673-b76766a89d57.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/e563f6da-b495-43d0-af5d-949ae60a1c95.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/243426d4-1322-4a58-a312-459a016d1207.jpeg@w_228,h_152"
                ]
            },
            {
                "id": "6319461065845964",
                "title": "余文乐结婚了？95%的人都没想到新娘是她！",
                "is_top": 0,
                "recommend": 0,
                "detail_url": "https://cpu.baidu.com/api/1022/ba3bef16/detail/6319461065845964/news?im=cbgJyhgD0OiiOPFECkw1h-U-qOaUNq-b_KuzbLvKbvfcMpCAvGgJ0Befujx4an5Ytuf9QuhjwxvU6Cs8I_Pc2igKWdyLpwfxUnkAKzCyn4IMFZGghYtKn1D9F6r71YTt2OY1Zc_uB9MctuEWTbRyDr3wUaM6CuSav7byKfUXRat5SSVlfvvSPQvbVXXEJOYRcLtogOf2D8kgsPfgEpHCtKDlqiYN_XNZ3KoE9OdYmb47Jow0zPIA9CnqTiSumwOo5BtC_R64VwKnQ9AWOwvqDBN-Cms-Qqu1mkePrS2UvE3jqasqmnxBsYZOAsjum_6Sm4sX8N0NQrMEe-MvSIOMGw&aid=XE4DeF2UNXgUB1fRgeA_AdfZENkB3D2ia-yKrnfml1fYtWIK_R1KA68QP8Obe9iOe7Z8CCyt_1nrQGdKMBBBSkgaZ6zSqF4SLyF_XRjoCfNOHuZn7P3zRzsdh0y796CBj8jwcl_mwyMhLLp62xvgR_DAXr7DD6y_9_wwEHObyUZaGjXVyKyFZPPJFMAuoa93kVO5BtTR6MZyuINbMHMttvars8ExCYHR6XWQLp2rGX41ZC2enY4b5BL0tiJLV99jdlTY6zK9z6taYLstNC9Qz3VkgiF75nccmbVwwgrgODOXID5ZomgEb5Ad5xcAr8e_wjcq8dQCqlzzvLQeWhjmpg&scene=0&logid=8180611633823215125381469921191794&no_list=1&foward=api&api_version=2",
                "source": "北青网",
                "update_time": "2017-12-06 05:08:17",
                "info_type": 3,
                "cat_id": 1001,
                "cat_name": "娱乐",
                "author_id": 0,
                "comment_counts": 0,
                "type": "news",
                "images": [
                    "https://publish-pic-cpu.baidu.com/1374eb45-72bb-40a8-9289-f2601121c030.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/9840afab-b1d9-4a3b-8935-6a8466dbe172.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/8fef14e3-8a75-4c29-9fe5-5c7bed39456b.jpeg@w_228,h_152"
                ]
            },
            {
                "id": "6309720482671789",
                "title": "何炅忍不住落泪，吴昕解约维嘉停录，背后原因太心痛！",
                "is_top": 0,
                "recommend": 0,
                "detail_url": "https://cpu.baidu.com/api/1022/ba3bef16/detail/6309720482671789/news?im=cbgJyhgD0OiiOPFECkw1h-U-qOaUNq-b_KuzbLvKbvfcMpCAvGgJ0Befujx4an5Ytuf9QuhjwxvU6Cs8I_Pc2igKWdyLpwfxUnkAKzCyn4IMFZGghYtKn1D9F6r71YTt2OY1Zc_uB9MctuEWTbRyDr3wUaM6CuSav7byKfUXRat5SSVlfvvSPQvbVXXEJOYRcLtogOf2D8kgsPfgEpHCtKDlqiYN_XNZ3KoE9OdYmb47Jow0zPIA9CnqTiSumwOo5BtC_R64VwKnQ9AWOwvqDBN-Cms-Qqu1mkePrS2UvE3jqasqmnxBsYZOAsjum_6Sm4sX8N0NQrMEe-MvSIOMGw&aid=XE4DeF2UNXgUB1fRgeA_AdfZENkB3D2ia-yKrnfml1fYtWIK_R1KA68QP8Obe9iOe7Z8CCyt_1nrQGdKMBBBSkgaZ6zSqF4SLyF_XRjoCfNOHuZn7P3zRzsdh0y796CBj8jwcl_mwyMhLLp62xvgR_DAXr7DD6y_9_wwEHObyUZaGjXVyKyFZPPJFMAuoa93kVO5BtTR6MZyuINbMHMttvars8ExCYHR6XWQLp2rGX41ZC2enY4b5BL0tiJLV99jdlTY6zK9z6taYLstNC9Qz3VkgiF75nccmbVwwgrgODOXID5ZomgEb5Ad5xcAr8e_wjcq8dQCqlzzvLQeWhjmpg&scene=0&logid=8180611633823215125381469921191794&no_list=1&foward=api&api_version=2",
                "source": "天涯的天涯",
                "update_time": "2017-12-05 22:35:31",
                "info_type": 3,
                "cat_id": 1001,
                "cat_name": "娱乐",
                "author_id": 0,
                "comment_counts": 0,
                "type": "news",
                "images": [
                    "https://publish-pic-cpu.baidu.com/d86b5138-711e-4f09-80c8-6c4e1d31e2cd.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/f10458c7-4080-4f04-8a19-d331d4a26483.jpeg@w_228,h_152",
                    "https://publish-pic-cpu.baidu.com/549d5f8e-e8e3-47f4-a6a9-2b8022024e72.jpeg@w_228,h_152"
                ]
            }]', true);


        $response->json(['code' => 200, 'msg' => 'success', 'total' => 10, 'data' => $data]);
    }
}
