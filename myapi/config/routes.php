<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


// 获取xd信息流提供给内部app
// Route::post('info/getxdinfo',  ['middleware' => 'btoken','uses' => 'InfoController@getXdInfo']);

Route::post('info/getxdinfo',  ['middleware' => 'btoken','uses' => 'InfoController@getBaiduFeeds']);

// 获取xd信息流提供给第三方 API 版
//Route::post('info/getxdtpos',  ['middleware' => 'btoken','uses' => 'InfoController@getXdInfoForExternal']);
Route::post('info/getxdtpos',  ['middleware' => 'btoken','uses' => 'InfoController@getBaiduFeedsExternal']);

// 获取xd信息流提供内部H5页面
//Route::post('info/geth5xdinfo',  ['middleware' => 'btoken', 'uses' => 'InfoController@getH5XdInfo']);
Route::post('info/geth5xdinfo',  ['middleware' => 'btoken', 'uses' => 'InfoController@getBaiduFeedsJs']);

// 获取xd信息流提供给第三方 JS 版
Route::post('info/getifapi',  ['middleware' => 'btoken', 'uses' => 'InfoController@getBaiduFeedsJsExternal']);
Route::post('info/gettest',  ['uses' => 'InfoController@getBaiduFeedsJsTest']);
// android app 版本升级检测接口
Route::post('info/update',  ['middleware' => 'btoken','uses' => 'InfoController@appversionCheck']);

// app 启动时开屏广告
Route::post('info/init',  ['middleware' => 'btoken','uses' => 'InfoController@getInitAd']);

// app 启动时记录地理位置信息
Route::post('info/geolog',  ['middleware' => 'btoken','uses' => 'InfoController@recordGeoLog']);

// app 记录点击详情页
Route::post('info/detaillog',  ['middleware' => 'btoken','uses' => 'InfoController@recordDetailLog']);


// 注册
Route::post('buser/register', ['middleware' => 'btoken', 'uses' => 'BuserController@postRegister']);

// 登录
Route::post('buser/login',  ['middleware' => 'btoken', 'uses' => 'BuserController@postLogin']);

// 登出
Route::post('buser/logout',  ['middleware' => 'btoken', 'uses' => 'BuserController@postLogout']);

// 修改密码
Route::post('buser/editpwd',  ['middleware' => 'btoken','uses' => 'BuserController@editPassword']);

// 找回密码--重设密码
Route::post('buser/resetpwd',  ['middleware' => 'btoken','uses' => 'BuserController@resetPassword']);

// 信息流浏览历史接口
Route::post('buser/history',  ['middleware' => 'btoken', 'uses' => 'BuserController@postHistory']);

// 信息流个人中心我的历史接口
Route::post('buser/ghistory',  ['middleware' => 'btoken', 'uses' => 'BuserController@getHistories']);

// 信息流清空历史接口
Route::post('buser/dhistory',  ['middleware' => 'btoken', 'uses' => 'BuserController@emptyHistories']);

// 信息流关注作者接口
Route::post('buser/follow',  ['middleware' => 'btoken', 'uses' => 'BuserController@postFollow']);

// 信息流取消关注作者接口
Route::post('buser/unfollow',  ['middleware' => 'btoken', 'uses' => 'BuserController@postUnfollow']);

// 信息流个人中心我的关注接口
Route::post('buser/gfollow',  ['middleware' => 'btoken', 'uses' => 'BuserController@getFollows']);

// 信息流写评论接口
Route::post('buser/wcomment',  ['middleware' => 'btoken', 'uses' => 'BuserController@postComment']);

// 信息流获取评论接口
Route::post('buser/gcomment',  ['middleware' => 'btoken', 'uses' => 'BuserController@getComments']);

// 信息流清空评论接口
Route::post('buser/dcomment',  ['middleware' => 'btoken', 'uses' => 'BuserController@emptyComments']);

// 信息流是否收藏接口
Route::post('buser/iscollect',  ['middleware' => 'btoken', 'uses' => 'BuserController@isCollect']);

// 信息流收藏接口
Route::post('buser/collect',  ['middleware' => 'btoken', 'uses' => 'BuserController@postCollect']);

// 信息流取消收藏接口
Route::post('buser/uncollect',  ['middleware' => 'btoken', 'uses' => 'BuserController@postUncollect']);

// 信息流个人中心我的收藏接口
Route::post('buser/gcollect',  ['middleware' => 'btoken', 'uses' => 'BuserController@getCollections']);

// 信息流 投诉/举报/反馈 接口
Route::post('buser/feedback',  ['middleware' => 'btoken', 'uses' => 'BuserController@feedBack']);

// 信息流反馈接口

// 信息流发送注册短信
Route::post('buser/sendmsg',  ['middleware' => 'btoken', 'uses' => 'BuserController@sendMessage']);

// 信息流获取积分接口
//Route::post('buser/getpoint',  ['middleware' => 'btoken','uses' => 'BaccountController@getPoint']);


// 获取定位接口
Route::post('buser/getlocation',  ['middleware' => 'btoken','uses' => 'BuserController@getLocation']);



// 信息流发送验证码邮件
Route::post('buser/sendemail',  ['middleware' => 'btoken', 'uses' => 'BuserController@sendValidEmail']);


// 信息流用户通知信息
Route::post('buser/notices',  ['middleware' => 'btoken', 'uses' => 'BuserController@getNoticeList']);
Route::post('buser/unread',  ['middleware' => 'btoken', 'uses' => 'BuserController@isUnreadNotice']);

// 信息流用户改变积分（未用到）
Route::post('buser/changecredit',  ['middleware' => 'btoken', 'uses' => 'BuserController@changeCredit']);

// 信息流用户完善资料接口
Route::post('buser/complete',  ['middleware' => 'btoken', 'uses' => 'BuserController@completeUserInfo']);


// 获取会员积分信息
Route::post('buser/getcredit',  ['middleware' => 'btoken', 'uses' => 'BuserController@getCredit']);

// 获取会员积分明细信息
Route::post('buser/getcreditlog',  ['middleware' => 'btoken', 'uses' => 'BuserController@getCreditLog']);

Route::post('ssp/add',  ['uses' => 'StatisticsController@add']);