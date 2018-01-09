<?php

class BuserController  extends BaseController{

    protected $redisKey = 'ifapi_valid_num:';
    protected $redisExpire = 1800;

    const ACTION_LOGIN = 1;  // 登录
    const ACTION_LOGOUT = 2; // 登出

    /**
    * 用户注册(已通过)
    *
    */
    public function postRegister(Request $request, Response $response)
    {
        // 注册方式 1=手机 2=邮箱
        $regtype    = $request->input('regtype', 1);
        $mobile     = $request->input('mobile', '');
        $email      = $request->input('email', '');
        $checknum   = $request->input('checknum', '');
        if (1 == $regtype) {
            $field = $mobile;
        } else {
            $field = $email;
        }
        $validnum   = OpRedis::getRedisHashValue($this->redisKey, $field);
        if (false === $validnum) {
            $response->json(['code' => 405, 'msg' => '验证码已过期，请重新获取']);
        }
        if ($checknum != $validnum) {
            $msg = (1 == $regtype) ? '手机验证码不正确' : '邮箱验证码不正确';
            $response->json(['code' => 406, 'msg' => $msg]);
        }
        if (1 == $regtype) {
            $nickname = 'qy' . substr($mobile, 5, 6) . '_' . date('Y');
            if (trim($mobile) == '') {
                $response->json(['code' => 402, 'msg' => '请填写手机号码']);
            }

            if (!is_mobile_no($mobile)) {
                $response->json(['code' => 403, 'msg' => '手机号码不正确']);
            }
            $condition = ['f_mobile' => $mobile];
        } else {
            $nickname = 'qy' . substr($email, 0, 6) . '_' . date('Y');
            if (trim($email) == '') {
                $response->json(['code' => 402, 'msg' => '请填写邮箱地址']);
            }
            if (!is_email($email)) {
                $response->json(['code' => 403, 'msg' => '邮箱地址不正确']);
            }
            $condition = ['f_email' => $email];
        }

        $password = $request->input('pwd','');

        if (trim($password) == '') {
            $response->json(['code' => 404, 'msg' => '请填写密码']);
        }
        $is_exist = Model::getFirstData('users', $condition);
        if ($is_exist) {
            $response->json(['code' => 401, 'msg' => '该账户已经注册过']);
        }
        $regtime = getCurrentTime();
        $regdate = getCurrentDate();

        $user = new stdClass;
        $user->f_mobile     = $mobile;
        $user->f_regtype    = $regtype;
        $user->f_email      = $email;
        $user->f_password   = md5($password);
        $user->f_nickname   = $nickname;
        $user->f_last_login = $regtime;
        $user->f_last_ip    = $request->input('ip', '');
        $user->f_addtime    = $regtime;
        $user_id = Model::saveData('users', $user);


        // 设备类型 1=IOS 2=Android
        $devtype    = $request->input('device_type', 2);

        if (2 == $devtype) {
            // android 唯一标识
            $udid = $request->input('androidid','');
        } else {
            $udid = $request->input('idfa','');
        }

        // 设备mac地址
        $mac  = $request->input('mac','');
        // 设备id udid MD5加密
        $deviceid   = md5($udid);

        //绑定用户设备
        $device = new stdClass;
        $device->f_device_type = $devtype;
        $device->f_udid        = $udid;
        $device->f_mac         = $mac;
        $device->f_vendor      = $request->input('vendor', ''); // 设备厂商
        $device->f_model       = $request->input('model', '');  // 设备型号
        $device->f_deviceid    = $deviceid;
        $device->f_user_id     = $user_id;
        $device->f_addtime     = $regtime;
        Model::saveData('device', $device);

        //绑定用户账户
        $account = new stdClass;
        $account->f_user_id    = $user_id;
        $account->f_deviceid   = $deviceid;
        $account->f_addtime    = $regtime;
        Model::saveData('account', $account);

        // 记录积分日志
        $rule = Model::getFirstData('credit_rules', [
            'f_rule' => 1,
            'f_flag' => 1
        ]);
        $number = 0;
        if ($rule) {
            $number = $rule->f_number;
            OpCredit::addCreditLog($user_id, $rule);
        }
        // 绑定用户积分表
        $credit = new stdClass;
        $credit->f_user_id     = $user_id;
        $credit->f_number      = $number;
        $credit->f_addtime     = $regtime;
        $credit->f_adddate     = $regdate;
        Model::saveData('user_credit', $credit);

        OpRedis::delRedisHashValue($this->redisKey, $field);

        $response->json(['code' => 200, 'msg' => 'success', 'data' => ['username' => $nickname, 'userid' => $user_id]]);
    }

    /**
    * 用户登录(已通过)
    *
    */
    public function postLogin(Request $request, Response $response)
    {
        $mobile = $request->input('mobile', '');

        if (trim($mobile) == '') {
            $response->json(['code' => 402, 'msg' => '请填写账户名']);
        }

        $password  = $request->input('pwd','');
        if (trim($password) == '') {
            $response->json(['code' => 403, 'msg' => '请填写密码']);
        }

        $condition = [
            'f_password' => md5($password)
        ];
        if (is_mobile_no($mobile)) {
            $condition['f_mobile'] = $mobile;
        }
        if (is_email($mobile)) {
            $condition['f_email']  = $mobile;
        }


        $userinfo = Model::getFirstData('users', $condition);
        if ($userinfo) {
            $user_id = $userinfo->f_id;
            $nickname = $userinfo->f_nickname;

            $update = ['f_last_login' => getCurrentTime()];
            $last_ip = get_client_ip();
            $last_ip && $update['f_last_ip'] = $last_ip;
            //更新最近登录ip和时间
            Model::updateUserById($user_id, $update);

            // 记录登录日志
            Model::saveData('user_login_log', [
                'f_user_id' => $user_id,
                'f_action' => self::ACTION_LOGIN,
                'f_addtime' => getCurrentTime()
            ]);

            $response->json(['code' => 200, 'msg' => 'success', 'data' => ['username' => $nickname, 'userid' => $user_id]]);
        } else{
            $response->json(['code' => 401, 'msg' => '账号或密码错误']);
        }
    }

    /**
     * 趣阅头条app 用户登出
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function postLogout(Request $request, Response $response)
    {
        $user_id = $request->input('userid');

        $user = Model::getUserById($user_id);
        // 记录登出日志
        Model::saveData('user_login_log', [
            'f_user_id' => $user_id,
            'f_action' => self::ACTION_LOGOUT,
            'f_mobile' => $user->f_mobile,
            'f_email' => $user->f_email,
            'f_addtime' => getCurrentTime()
        ]);
        $response->json(['code' => 200, 'msg' => 'success']);
    }

    /**
    * 浏览器用户修改密码(已通过)
    *
    */
    public function editPassword(Request $request, Response $response)
    {
        $mobile = $request->input('username', '');

        if (trim($mobile) == '') {
            $response->json(['code' => 402, 'msg' => '请填写账户名']);
        }

        $old_pwd  = $request->input('oldpwd','');

        if (trim($old_pwd) == '') {
            $response->json(['code' => 403, 'msg' => '请填写旧密码']);
        }

        $new_pwd  = $request->input('newpwd','');

        if (trim($old_pwd) == '') {
            $response->json(['code' => 404, 'msg' => '请填写新密码']);
        }
        $condition = [
            'f_password' => md5($old_pwd)
        ];
        if (is_mobile_no($mobile)) {
            $condition['f_mobile'] = $mobile;
        }
        if (is_email($mobile)) {
            $condition['f_email']  = $mobile;
        }
        $userinfo = Model::getFirstData('users', $condition);
        if ($userinfo) {
            //更新密码
            Model::updateUserById($userinfo->f_id, ['f_password' => md5($new_pwd)]);
            $response->json(['code' => 200, 'msg' => 'success']);
        } else{
            $response->json(['code' => 401, 'msg' => '旧密码错误']);
        }
    }

    /**
     * 找回密码最后一步，重设密码
     * @param  Request  $request  [description]
     * @param  Response $response [description]
     * @return Response           [description]
     */
    public function resetPassword(Request $request, Response $response)
    {
        $username = $request->input('username', '');
        $newpwd  = trim($request->input('newpwd', ''));
        $checknum = $request->input('checknum', '');
        $validnum = OpRedis::getRedisHashValue($this->redisKey, $username);
        if ('' === $newpwd) {
            $response->json(['code' => 402, 'msg' => '密码不能为空']);
        }
        // if (false === $validnum) {
        //     $response->json(['code' => 405, 'msg' => '验证码已过期，请重新获取']);
        // }
        $msg = '';
        if (is_mobile_no($username)) {
            $condition['f_mobile'] = $username;
            $msg = '验证码不正确';
        } elseif (is_email($username)) {
            $condition['f_email']  = $username;
            $msg = '验证码不正确';
        } else {
            $msg = '请填写正确的账号';
            $response->json(['code' => 405, 'msg' => $msg]);
        }
        if ($checknum != $validnum) {
            $response->json(['code' => 406, 'msg' => $msg]);
        }

        $userinfo = Model::getFirstData('users', $condition);
        if ($userinfo) {
            $user_id = $userinfo->f_id;
            $nickname = $userinfo->f_nickname;
            //更新密码
            Model::updateUserById($user_id, ['f_password' => md5($newpwd)]);
            // 清除验证码，使之过期
            OpRedis::delRedisHashValue($this->redisKey, $username);

            $response->json(['code' => 200, 'msg' => 'success', 'data' => ['username' => $nickname, 'userid' => $user_id]]);
        } else{
            $response->json(['code' => 401, 'msg' => '账号错误']);
        }
    }

    /**
     * 我的 -- 关注(已通过)
     */
    public function getFollows(Request $request, Response $response)
    {
        $page = $request->input('page', 1);
        $limit = config('app.info_count');
        $user_id = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code' => 401, 'msg' => '用户标识不正确']);
        }

        $follows = Model::getData('follows', ['f_user_id' => $user_id, 'f_is_follow' => 1], $page, $limit, 'f_author_id');
        $authorid = [];
        foreach ($follows as $key => $value) {
            $authorid[] = $value->f_author_id;
        }
        $authorids = implode(',', $authorid);
        $lists = (new DB)->table('authors')->where('f_id', 'in', '(' . $authorids . ')')
            ->get(['f_id','f_penname','f_portrait']);

        $data = [];
        foreach ($lists as $v) {
            $row = [
                'authorid' => $v->f_id,
                'authorname' => $v->f_penname,
                'portrait' => $v->f_portrait
            ];
            $data[] = $row;
        }
        $response->json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }


    /**
     * 我的 --- 收藏(已通过)
     */
    public function getCollections(Request $request, Response $response)
    {
        $page = $request->input('page', 1);
        $limit = config('app.collection_count');
        $user_id = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code' => 401, 'msg' => '用户标识不正确']);
        }

        $lists = Model::getData('collections', ['f_user_id' => $user_id, 'f_is_collect' => 1], $page, $limit);

        $data = [];
        foreach ($lists as $v) {
            $row = [
                'id'             => $v->f_id,
                'news_id'        => $v->f_news_id,
                'title'          => $v->f_title,
                'images'         => json_decode($v->f_images, true),
                'detail_url'     => $v->f_detail_url,
                'source'         => $v->f_source,
                'info_type'      => $v->f_info_type,
                'cat_id'         => $v->f_cat_id,
                'cat_name'       => $v->f_cat_name,
                'comment_counts' => $v->f_comment_counts,
                'addtime'        => $v->f_addtime,
                'adddate'        => strtodate($v->f_addtime)
            ];
            $data[] = $row;
        }
        $response->json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /**
     * 我的 -- 历史记录(已通过)
     */
    public function getHistories(Request $request, Response $response)
    {
        $page = $request->input('page', 1);
        $limit = config('app.history_count');
        $user_id = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code' => 401, 'msg' => '用户标识不正确']);
        }

        $lists = Model::getData('histories', ['f_user_id' => $user_id, 'f_is_del' => 0], $page, $limit);
        $data = [];
        foreach ($lists as $v) {
            $row = [
                'id'             => $v->f_id,
                'news_id'        => $v->f_news_id,
                'title'          => $v->f_title,
                'images'         => $v->f_images,
                'detail_url'     => $v->f_detail_url,
                'source'         => $v->f_source,
                'info_type'      => $v->f_info_type,
                'cat_id'         => $v->f_cat_id,
                'cat_name'       => $v->f_cat_name,
                'comment_counts' => $v->f_comment_counts,
                'addtime'        => $v->f_addtime,
                'adddate'        => strtodate($v->f_addtime)
            ];
            $data[] = $row;
        }
        $response->json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }
    /**
     * 我的 -- 清空历史(已通过)
     */
    public function emptyHistories(Request $request, Response $response)
    {
        $user_id = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code'=> 401, 'msg' => '用户标识不正确']);
        }
        $rst = Model::updateData('histories', ['f_user_id' => $user_id], ['f_is_del' => 1]);
        if (FALSE !== $rst) {
            $response->json(['code' => 200, 'msg' => 'success']);
        }
        $response->json(['code' => 402, 'msg' => '清空失败']);

    }
    /**
     * 我的 -- 评论(已通过)
     */
    public function getComments(Request $request, Response $response)
    {
        $page = $request->input('page', 1);
        $limit = config('app.comment_count');

        $user_id = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code'=>401, 'msg' => '用户标识不正确']);
        }
        $lists = Model::getData('comments', ['f_user_id' => $user_id, 'f_is_del' => 0, 'f_pid' => 0], $page, $limit);
        $data = [];
        foreach ($lists as $v) {
            $row = [
                'id'             => $v->f_id,
                'news_id'        => $v->f_news_id,
                'title'          => $v->f_title,
                'images'         => $v->f_images,
                'detail_url'     => $v->f_detail_url,
                'source'         => $v->f_source,
                'info_type'      => $v->f_info_type,
                'cat_id'         => $v->f_cat_id,
                'cat_name'       => $v->f_cat_name,
                'comment_counts' => $v->f_comment_counts,
                'addtime'        => $v->f_addtime,
                'adddate'        => strtodate($v->f_addtime)
            ];
            $data[] = $row;
        }
        $response->json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }
    /**
     * 我的 -- 清空评论(已通过)
     */
    public function emptyComments(Request $request, Response $response)
    {
        $user_id = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code' => 401, 'msg' => '用户标识不正确']);
        }
        $rst = Model::updateData('comments', ['f_user_id' => $user_id], ['f_is_del' => 1]);
        if (FALSE !== $rst) {
            $response->json(['code' => 200, 'msg' => 'success']);
        }
        $response->json(['code' => 402, 'msg' => '清空失败']);
    }
    /**
     * 关注文章作者接口(已通过)
     */
    public function postFollow(Request $request, Response $response)
    {
        $user_id = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code' => 402, 'msg' => '用户标识不正确']);
        }

        $author_id = intval($request->input('authorid', 0));

        if ($author_id <= 0) {
            $response->json(['code' => 403, 'msg' => '作者标识不正确']);
        }

        $condition = ['f_user_id' => $user_id, 'f_author_id' => $author_id];
        $fill = ['f_is_follow' => 1];
        //$rst = Follow::updateOrCreate($condition, $fill);

        $follows = Model::getFirstData('follows', $condition);
        if ($follows) {
            $rst = Model::updateDataById('follows', $follows->f_id, $fill);
        } else {
            $rst = Model::saveData('follows', array_merge($condition, $fill));
        }

        if ($rst) {
            $response->json(['code' => 200, 'msg' => 'success']);
        }
        $response->json(['code' => 401, 'msg' => '关注失败']);
    }
    /**
     * 取消关注文章作者接口(已通过)
     */
    public function postUnfollow(Request $request, Response $response)
    {
        $user_id = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code'=>402, 'msg' => '用户标识不正确']);
        }

        $author_id = intval($request->input('authorid', 0));

        if ($author_id <= 0) {
            $response->json(['code' => 403, 'msg' => '作者标识不正确']);
        }
        $rst = Model::updateData('follows', ['f_user_id' => $user_id, 'f_author_id' => $author_id], ['f_is_follow' => 0]);
        if (FALSE !== $rst) {
            $response->json(['code' => 200, 'msg' => 'success']);
        }
        $response->json(['code' => 401, 'msg' => '取消失败']);
    }
    /**
     * 发表评论接口(已通过)
     */
    public function postComment(Request $request, Response $response)
    {
        $user_id = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code' => 402, 'msg' => '用户标识不正确']);
        }

        $comments  = trim($request->input('comment', ''));
        if ($comments == '') {
            $response->json(['code' => 403, 'msg' => '请填写评论内容']);
        }

        $news_id = intval($request->input('news_id', 0));
        if ($news_id <= 0) {
            $response->json(['code' => 404, 'msg' => '信息标识不正确']);
        }

        $comment = new stdClass;
        $comment->f_user_id        = $user_id;
        $comment->f_news_id         = $news_id;
        $comment->f_title           = $request->input('title', '');
        $comment->f_images          = json_encode($request->input('images', []));
        $comment->f_detail_url      = $request->input('detail_url', '');
        $comment->f_source          = $request->input('source', '');
        $comment->f_info_type       = $request->input('info_type', 1);
        $comment->f_cat_id          = $request->input('cat_id', 1001);
        $comment->f_cat_name        = $request->input('cat_name', '');
        $comment->f_comment_counts  = $request->input('comment_counts', 0);
        $comment->f_comments        = $comments;
        $comment->f_pid             = $request->input('pid', 0);
        $comment->f_status          = 0;
        $comment->f_is_del          = 0;
        $comment->f_channel         = $request->input('channel', 1);;
        $comment->f_addtime         = getCurrentTime();
        $rst = Model::saveData('comments', $comment);
        if ($rst) {
            $response->json(['code' => 200, 'msg' => 'success']);
        }
        $response->json(['code' => 401, 'msg' => '评论失败']);
    }
    /**
     * 是否收藏接口接口
     */
    public function isCollect(Request $request, Response $response)
    {
        $user_id  = intval($request->input('userid', 0));
        if ($user_id <= 0) {
            $response->json(['code' => 402, 'msg' => '用户标识不正确']);
        }

        $news_id = intval($request->input('news_id', 0));
        if ($news_id <= 0) {
            $response->json(['code' => 404, 'msg' => '信息标识不正确']);
        }

        $condition = ['f_user_id' => $user_id, 'f_news_id' => $news_id, 'f_is_collect' => 1];
        if (Model::getFirstData('collections', $condition)) {
            $response->json(['code' => 200, 'msg' => 'success']);
        }
        $response->json(['code' => 201, 'msg' => '未收藏']);
    }
    /**
     * @api {post} /buser/collect
     * @apiName 收藏接口
     * @apiGroup Buser
     *
     * @apiParam {Number} userid   用户id.
     * @apiParam {String} infoflag 信息标识.
     *
     * @apiSuccess {Number} code 状态码.
     * @apiSuccess {String} msg  提示信息.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg": "收藏成功"
     *     }
     */
    public function postCollect(Request $request, Response $response)
    {
        $user_id  = intval($request->input('userid', 0));
        if ($user_id <= 0) {
            $response->json(['code' => 402, 'msg' => '用户标识不正确']);
        }

        $news_id = intval($request->input('news_id', 0));
        if ($news_id <= 0) {
            $response->json(['code' => 404, 'msg' => '信息标识不正确']);
        }

        $condition = ['f_user_id' => $user_id, 'f_news_id' => $news_id];
        $collection = Model::getFirstData('collections', $condition);
        if ($collection) {
            Model::updateDataById('collections', $collection->f_id, ['f_is_collect' => 1]);
            $response->json(['code' => 200, 'msg' => 'success']);
        } else {
            $collection = new stdClass;
            $collection->f_user_id        = $user_id;
            $collection->f_news_id         = $news_id;
            $collection->f_title           = $request->input('title', '');
            $collection->f_images          = json_encode($request->input('images', []));
            $collection->f_detail_url      = $request->input('detail_url', '');
            $collection->f_source          = $request->input('source', '');
            $collection->f_info_type       = $request->input('info_type', '');
            $collection->f_cat_id          = $request->input('cat_id', 1001);
            $collection->f_cat_name        = $request->input('cat_name', '');
            $collection->f_comment_counts  = $request->input('comment_counts', 0);
            $collection->f_is_collect      = 1;
            $collection->f_addtime         = getCurrentTime();
            $rst = Model::saveData('collections', $collection);
            if ($rst) {
                $response->json(['code' => 200, 'msg' => 'success']);
            }
            $response->json(['code' => 401, 'msg' => '收藏失败']);
        }
    }
    /**
     * @api {post} /buser/uncollect
     * @apiName 取消收藏接口
     * @apiGroup Buser
     *
     * @apiParam {Number} userid   用户id.
     * @apiParam {String} infoflag 信息标识.
     *
     * @apiSuccess {Number} code 状态码.
     * @apiSuccess {String} msg  提示信息.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg": "取消成功"
     *     }
     */
    public function postUncollect(Request $request, Response $response)
    {
        $user_id  = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code' => 402, 'msg' => '用户标识不正确']);
        }

        $news_id = intval($request->input('news_id', 0));
        if ($news_id <= 0) {
            $response->json(['code' => 404, 'msg' => '信息标识不正确']);
        }

        $rst = Model::updateData('collections', ['f_user_id' => $user_id, 'f_news_id' => $news_id],['f_is_collect' => 0]);
        if (FALSE !== $rst) {
            $response->json(['code' => 200, 'msg' => 'success']);
        }
        $response->json(['code' => 401, 'msg' => '取消失败']);
    }
    /**
     * 记录浏览历史接口
     */
    public function postHistory(Request $request, Response $response)
    {
        $user_id = intval($request->input('userid', 0));

        if ($user_id <= 0) {
            $response->json(['code' => 402, 'msg' => '用户标识不正确']);
        }

        $news_id = intval($request->input('news_id', 0));
        if ($news_id <= 0) {
            $response->json(['code' => 404, 'msg' => '信息标识不正确']);
        }
        $condition = ['f_user_id' => $user_id, 'f_news_id' => $news_id];
        $history = Model::getFirstData('histories', $condition);
        if ($history) {
            Model::updateDataById('histories', $history->f_id, ['f_is_del' => 0]);
            $response->json(['code' => 200, 'msg' => 'success']);
        } else {
            $history = new stdClass;
            $history->f_user_id        = $user_id;
            $history->f_news_id         = $news_id;
            $history->f_title           = $request->input('title', '');
            $history->f_images          = json_encode($request->input('images', []));
            $history->f_detail_url      = $request->input('detail_url', '');
            $history->f_source          = $request->input('source', '');
            $history->f_info_type       = $request->input('info_type', '');
            $history->f_cat_id          = $request->input('cat_id', '');
            $history->f_cat_name        = $request->input('cat_name', '');
            $history->f_comment_counts  = $request->input('comment_counts', '');
            $history->f_is_del          = 0;
            $history->f_addtime         = getCurrentTime();
            $rst = Model::saveData('histories', $history);
            if ($rst) {
                $response->json(['code' => 200, 'msg' => 'success']);
            }
            $response->json(['code' => 401, 'msg' => 'FAIL']);
        }
    }

    /**
     * 分享接口
     */
    public function share(Request $request, Response $response)
    {

    }

    /**
     * 投诉/举报/反馈 接口
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function feedBack(Request $request, Response $response)
    {
        $data = [];
        $data['f_user_id'] = $request->input('userid', 0);
        $data['f_type']    = $request->input('type', 1);
        $data['f_contact'] = $request->input('contact', '');
        $data['f_content'] = $request->input('content', '');
        $data['f_addtime'] = getCurrentTime();
        $data['f_note']    = '';

        $isOK = Model::saveData('feedbacks', $data);
        if ($isOK) {
            $response->json(['code' => 200, 'msg' => 'success']);
        } else {
            $response->json(['code' => 401, 'msg' => 'FAIL']);
        }

    }
    /**
     * @api {post} /buser/sendmsg
     * @apiName 发送手机验证码
     * @apiGroup Buser
     *
     * @apiParam {String} mobile 手机号码.
     *
     * @apiSuccess {Number} code 状态码.
     * @apiSuccess {String} msg  提示信息.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg": "短信验证码已发送至您的手机，请注意查收"
     *     }
     */
    public function sendMessage(Request $request, Response $response)
    {
        $mobile = $request->input('mobile','');
        if(!is_mobile_no($mobile)) {
            $response->json(['msg' => '手机号码不正确','code' => 402]);
        }

        $appid = 1400033561;
        $appkey = "516485f063ca2e370c248909719b3bca";
        $time = time();
        $random = mt_rand(1000, 9999);
        $url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=".$appid."&random=".$random;
        //验证码内容
        $randomNum = mt_rand(1000, 9999);

        //$msg = "您的短信验证码为1234,30分钟内输入有效";
        $sig = hash("sha256", "appkey=".$appkey."&random=".$random."&time=".$time."&mobile=".$mobile, FALSE);

        $data = ['tel' => ['nationcode' => '86','mobile' => $mobile],'sign' => '', 'tpl_id' => '24523','params' => [$randomNum], 'sig' => $sig, 'time' => $time, 'extend' => '', 'ext' => ''];

        if(!sendCurlPost($url, $data)){
            $response->json(['msg' => '发送验证码失败，请重新获取','code' => 401]);
        }
        OpRedis::setRedisHashValue($this->redisKey, $mobile, $randomNum, $this->redisExpire);
        $response->json(['msg' => 'success', 'code' => 200]);
    }

    /**
     * 获取定位接口
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function getLocation(Request $request, Response $response)
    {
        $longitude = intval($request->input('longitude') * 1000000) / 1000000;
        $latitude  = intval($request->input('latitude') * 1000000) / 1000000;
        $location = $longitude . ',' . $latitude;
        $param = $query = [
            'location' => $location,
            'key'      => config('app.amap.appkey')
        ];
        ksort($param);
        $sig = md5(build_http_query($param).config('app.amap.secret'));
        $query['sig'] = $sig;
        $amapUrl = config('app.amap.urlprefix') . build_http_query($query);
        $infos = sendCurlGet($amapUrl);
        $infos = json_decode($infos);
        if ($infos->infocode == 10000) {
            $adcode = $infos->regeocode->addressComponent->adcode;
            $area = Model::getFirstData('country_city_code', ['f_coun_gb' => $adcode]);
            $rep = [
                'code'      => 200,
                'msg'       => 'success',
                'data'      => [
                    'address'   => $infos->regeocode->formatted_address,
                    'country'   => $infos->regeocode->addressComponent->country,
                    'province'  => $infos->regeocode->addressComponent->province,
                    'city'      => $infos->regeocode->addressComponent->city,
                    'adcode'    => $adcode,
                    'district'  => $infos->regeocode->addressComponent->district,
                    'street'    => $infos->regeocode->addressComponent->streetNumber->street,
                    'number'    => $infos->regeocode->addressComponent->streetNumber->number,
                    'pv_adcode' => $area->f_prov_gb,
                    'ct_adcode' => $area->f_city_gb,
                    'dt_adcode' => $adcode,
                ]
            ];
        } else {
            $rep = [
                'code'      => 400,
                'amapcode'  => $infos->infocode,
                'msg'       => $infos->info,
            ];
        }
        $response->json($rep);

    }

    /**
     * 趣阅头条app 发送验证码邮件
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function sendValidEmail(Request $request, Response $response)
    {
        $params = [];
        $email = trim($request->input('to'));
        if (!is_email($email)) {
            $response->json(['msg' => '邮箱地址不正确','code' => 402]);
        }
        $params['toAddress'] = [$email];
        $params['Subject'] = '趣阅头条';

        //验证码内容
        $randomNum = mt_rand(1000, 9999);
        $type = $request->input('type');
        $body = '您的邮件验证码为 ' . $randomNum . ' ，30分钟内输入有效。';
        $params['body'] = $body;
        $isSend = OpEmail::sendEmail($params);
        if ($isSend) {
            OpRedis::setRedisHashValue($this->redisKey, $email, $randomNum, $this->redisExpire);
            $response->json(['msg' => 'success', 'code' => 200]);
        } else {
            $response->json(['msg' => '发送失败', 'code' => 401]);
        }
    }

    /**
     * 趣阅头条app 获取用户通知列表
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function getNoticeList(Request $request, Response $response)
    {
        $userid  = $request->input('userid', 0);
        $page  = $request->input('page', 1);
        $limit = 10;
        $where = ['f_user_id'=>$userid];
        $table = 'user_messages';
        $notices = Model::getData($table, $where, $page, $limit);
        Model::updateData($table, $where, ['f_status' => 1]);
        $data = [];
        foreach ($notices as $key => $value) {
            $data[$key]['title'] = $value->f_title;
            $data[$key]['addtime'] = $value->f_addtime;
        }
        $response->json(['code' => 200, 'msg' => 'success', 'data' => $data]);
    }

    /**
     * 趣阅头条app 获取用户是否有未读通知
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function isUnreadNotice(Request $request, Response $response)
    {
        $userid  = $request->input('userid', 0);
        if (!$userid) {
            $response->json(['code' => 400, 'msg' => '用户信息不正确']);
        }
        $where = ['f_user_id'=>$userid, 'f_status' => 0];
        $table = 'user_messages';
        $notices = Model::getFirstData($table, $where, 'count(*) num');
        $isUnread = boolval($notices->num);
        $response->json(['code' => 200, 'msg' => 'success', 'data' => ['isUnread' => $isUnread]]);
    }

    /**
     * 趣阅头条app 改变用户积分接口
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function changeCredit(Request $request, Response $response)
    {
        $user_id = $request->input('userid', 0);
        $rule_id = $request->input('ruleid', 0);
        $code = OpCredit::changeCredit($user_id, $rule_id);
        switch ($code) {
            case 400:
                $msg = '积分规则不对';
                break;
            case 401:
                $msg = '该帐号积分账户不正常';
                break;
            case 402:
                $msg = '更新积分失败';
                break;
            case 200:
                $msg = '更新积分成功';
                break;
            default:
                $msg = '';
                break;
        }
        $response->json(['code' => $code, 'msg' => $msg]);

    }

    /**
     * 趣阅头条app 获取会员积分明细信息
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function getCreditLog(Request $request, Response $response)
    {
        $user_id = $request->input('userid', 0);
        $page = $request->input('page', 1);
        $logs = OpCredit::getCreditLog($user_id, $page, 10);
        $data = [];
        foreach ($logs as $key => $value) {
            $tmp = [
                'addtime' => $value->f_addtime,
                'reason' => $value->f_name,
                'number' => $value->f_number,
            ];
            $data[$key] = $tmp;
        }
        if ($logs == 400) {
            $response->json(['code' => 400, 'msg' => '用户标识不正确']);
        } else {
            $response->json(['code' => 200, 'msg' => 'success', 'total'=> count($logs), 'data' => $data]);
        }
    }

    /**
     * 趣阅头条app 获取会员积分信息
     *
     * @param Request $request 请求参数Request $request
     * @param Response $response 响应参数Response $response
     *
     * @return Response
     */
    public function getCredit(Request $request, Response $response)
    {
        $user_id = $request->input('userid', 0);
        $credit = OpCredit::getCredit($user_id);
        if ($credit == 400) {
            $response->json(['code' => 400, 'msg' => '用户标识不正确']);
        } else {
            $response->json(['code' => 200, 'msg' => 'success', 'data' => ['creditnum' => intval($credit->f_number)]]);
        }
    }

    /**
     * 完善用户信息，增加积分
     * @param  Request  $request  [description]
     * @param  Response $response [description]
     * @return Response           [description]
     */
    public function completeUserInfo(Request $request, Response $response)
    {
        $user_id = $request->input('userid', 0);
        $user = Model::getUserById($user_id);
        $isFirst = !($user->f_mobile && $user->f_email);

        if (!$user) {
            $response->json(['code' => 400, 'msg' => '用户标识不正确']);
        }
        unset(
            $user->f_id,
            $user->f_updtime,
            $user->f_addtime,
            $user->f_password,
            $user->f_status,
            $user->f_portrait,
            $user->f_last_login,
            $user->f_last_ip
        );
        $checknum = $request->input('checknum', '');
        if ($checknum === '') {
            $response->json(['code' => 404, 'msg' => '验证码不正确']);
        }
        if ($user->f_regtype == 1) {
            // 手机注册的用户
            $email = $request->input('email', '');
            if (!is_email($email)) {
                $response->json(['code' => 405, 'msg' => '邮箱地址不正确']);
            }
            // 判定email是否已经注册
            $isEmailReg = Model::getFirstData('users', ['f_email' => $email]);
            if ($user->f_email != $email && $isEmailReg) {
                $response->json(['code' => 406, 'msg' => '此邮箱地址已注册']);
            }
            $field = $email;
            $user->f_email = $email;
        } elseif ($user->f_regtype == 2) {
            // 邮箱注册的用户
            $mobile = $request->input('mobile', '');
            if (!is_mobile_no($mobile)) {
                $response->json(['code' => 402, 'msg' => '手机号码不正确']);
            }
            // 判定mobile是否已经注册
            $isMobileReg = Model::getFirstData('users', ['f_mobile' => $mobile]);
            if ($user->f_mobile != $mobile && $isMobileReg) {
                $response->json(['code' => 403, 'msg' => '此手机号码已注册']);
            }
            $field = $mobile;
            $user->f_mobile = $mobile;
        }
        $validnum = OpRedis::getRedisHashValue($this->redisKey, $field);
        if ($validnum !== $checknum) {
            // 验证不通过
            $response->json(['code' => 404, 'msg' => '验证码不正确']);
        }
        // 第一次完善的时候加积分
        if ($isFirst) {
            // 记录积分日志,改变积分
            OpCredit::changeCredit($user_id, 2);
        }

        $nickname = $request->input('nickname', '');
        if ($nickname) {
            $user->f_nickname = $nickname;
        }
        $user->f_realname = $request->input('realname', '');
        $user->f_gender   = $request->input('gender', '');
        $user->f_live_place = $request->input('live_place', '');
        $user->f_interest = $request->input('interest', '');
        $user->f_profession = $request->input('profession', '');
        $user->f_birth_place = $request->input('birth_place', '');
        $user->f_birthday = $request->input('birthday', '');

        $isOk = Model::updateDataById('users', $user_id, json_decode(json_encode($user), true));
        if ($isOk) {
            // 清除验证码，使之过期
            OpRedis::delRedisHashValue($this->redisKey, $field);

            $response->json(['code' => 200, 'msg' => 'success']);
        } else {
            $response->json(['code' => 401, 'msg' => '保存失败，请重试']);
        }
    }
}



