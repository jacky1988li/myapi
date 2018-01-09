<?php

if (ONLINE) {
    $ssp_ad_api = [
        'url' => 'http://api.mssp.adunite.com/browserapi/browser.api',
        'threadurl' => 'http://api.mssp.adunite.com/browserapi/browser.api',
        //'threadurl' => 'http://api.mssp.adunite.com/browserthread/browserthread.api'
    ];
    $ssp_ad_js = [
        'url' => 'http://api.mssp.adunite.com/h5browser.api'
    ];
    $cpa_ad = [
        'url' => 'http://www.lxjcpaadmin.com/Api/advertisement',
        '_url' => '/Api/advertisement',
        'apikey' => '$#eWE56@#$fd%$^$%gg$#%34Rr%'
    ];
} else {
    $ssp_ad_api = [
        //'url' => 'http://192.168.0.250:9038/browser.php'
        'url' => 'http://192.168.0.250:9033/browserapi2/browser.api',
        'threadurl' => 'http://api.mssp.adunite.com/browserthread/browserthread.api'
    ];
    $ssp_ad_js = [
        'url' => 'http://192.168.0.250:9033/h5browser.api'
        //'url' => 'http://api.mssp.adunite.com/h5browser.api'
    ];
    $cpa_ad = [
        'url' => 'http://www.lxjcpaadmin.com/Api/advertisement',
        '_url' => '/Api/advertisement',
        'apikey' => '$#eWE56@#$fd%$^$%gg$#%34Rr%'
    ];
}
return [
	//分页大小
	'curl_timeout_ms' => 500,

    //分页大小
	'paginsize' => 20,

	//信息流返回条数
	'info_count' => 8,

    //返回广告条数
	'ad_count' => 0,

    //返回我的收藏条数
	'collection_count' => 10,

    //返回我的历史条数
	'history_count' => 10,

    //返回我的评论条数
	'comment_count' => 10,
    //xd 配置
    'xd_config' => [
        // 请求链接
        'url'      => 'http://api.touchxd.com/cu/cuInfo',
        // 渠道 ID，请联系商务提供
        'channelId'      => 912533208736817152,
        // 应用 ID，请联系商务提供
        'appId'          => 912535590140993536,
        // 广告位 id，请联系商务提供
        'slotId'         => 912535870584741888
    ],
    // 百度信息流配置
    'baidu_feeds' => [
        'appsid' => ['ios' => 'fc40b64a', 'android' => 'ba3bef16'],
        'token' => 'a4630e0c9157a7cabd28e7ad8',
        'url' => 'https://cpu-openapi.baidu.com/api/v2/data/list'
    ],
    //ssp api 广告配置
    'ssp_ad_api' => $ssp_ad_api,
    //ssp js 广告配置
    'ssp_ad_js' => $ssp_ad_js,
    'cpa_ad' => $cpa_ad,
    'amap' => [
        'secret' => 'edaee152b5fa013613853068db092508',
        'appkey' => 'aa36b2506730815d7be4cf847f63ad94',
        'urlprefix' => 'http://restapi.amap.com/v3/geocode/regeo?'
    ]
];
