<?php

class OpEmail {
    /**
     * 获取 redis 中hash类型的值
     * @author lichenglong
     * @param  array $params
     * @return mixed         返回值
     */
    public static function sendEmail($params = [])
    {
        $mail = new PHPMailer(); //实例化
        //$mail->SMTPDebug = 1; //开启debug
        $mail->IsSMTP(); // 启用SMTP
        $mail->Host       = "smtp.exmail.qq.com"; //SMTP服务器
        $mail->Port       = 465;  //邮件发送端口
        $mail->SMTPAuth   = true;  //启用SMTP认证
        $mail->SMTPSecure = "ssl";      // 打开SSL加密，这里是为了解决QQ企业邮箱的加密认证问题的~~
        $mail->CharSet    = "UTF-8"; //字符集
        $mail->Encoding   = "base64"; //编码方式

        $mail->Username   = "service@adunite.com";  //你的邮箱
        $mail->Password   = "Wx123123";  //你的密码
        $mail->Subject    = $params['Subject']; //邮件标题

        $mail->From       = "service@adunite.com";  //发件人地址（也就是你的邮箱）
        $mail->FromName   = "旺翔传媒";  //发件人姓名
        
        
        //收件人数组
        $toAddress = $params['toAddress'];
        //抄送地址 数组
        $ccAddress = empty($params['ccAddress']) ? [] : $params['ccAddress'];
        //隐藏抄送地址 数组
        $bccAddress = empty($params['bccAddress']) ? [] : $params['bccAddress'];

        //添加收件人
        foreach ($toAddress as $v) {
            $mail->AddAddress($v, "");//添加收件人（地址，昵称）
        }
        //添加抄送人
        foreach ($ccAddress as $v) {
            $mail->AddCC($v, "");//添加抄送人（地址，昵称）
        }
        //添加隐藏抄送人
        foreach ($bccAddress as $v) {
            $mail->AddBCC($v, "");//添加隐藏抄送人（地址，昵称）
        }
        if (isset($params['attachment']) && is_array($params['attachment'])) {
            foreach ($params['attachment'] as $key => $value) {
                if (!empty($value[0]) && !empty($value[1])) {
                    $mail->AddAttachment($value[0], $value[1]); // 添加附件,并指定名称
                }
            }
        }
        $mail->IsHTML(true); //支持html格式内容
        $mail->Body = $params['body']; //邮件主体内容
        $isSend = $mail->Send();

        return $isSend;
    }
}
