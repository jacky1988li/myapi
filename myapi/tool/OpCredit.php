<?php
/**
 * 积分操作类
 */
class OpCredit {
    const T_CREDIT = 'user_credit';
    const T_CREDIT_RULES = 'credit_rules';
    const T_CREDIT_LOG = 'credit_log';
    /**
     * 获取 redis 中hash类型的值
     * @author lichenglong
     * @param  string $rule_id   
     * @param  string $user_id  
     * @return int         返回值
            case 400: 积分规则不对
            case 401: 该帐号积分账户不正常
            case 402: 更新积分失败
            case 200: 更新积分成功
     */
    public static function changeCredit($user_id, $rule_id)
    {
        if (!$rule_id) {
            return 400;
        }
        $rule = Model::getFirstData(self::T_CREDIT_RULES, [
            'f_flag' => 1,
            'f_rule' => $rule_id
        ]);

        if ($rule) {
            
            self::addCreditLog($user_id, $rule);

            $credit = Model::getFirstData(self::T_CREDIT,
                ['f_user_id' => $user_id, 'f_flag' =>1], 
                ['f_id', 'f_number']
            );
            if ($credit) {
                $num = $rule->f_number + $credit->f_number;
                $isOK = Model::updateData(self::T_CREDIT, ['f_id' => $credit->f_id], ['f_number' => $num]);
                if ($isOK) {
                    return 200;
                } else {
                    return 402;
                }
                
            } else {
                return 401;
            }
        } else {
            return 400;
        }
    }

    public static function addCreditLog($user_id, $rule)
    {
        $creditLog = new stdClass();
        $creditLog->f_user_id     = $user_id;
        $creditLog->f_rule        = $rule->f_rule;
        $creditLog->f_name        = $rule->f_name;
        $creditLog->f_number      = $rule->f_number;
        $creditLog->f_addtime     = getCurrentTime();
        $creditLog->f_adddate     = getCurrentDate();
        Model::saveData(self::T_CREDIT_LOG, $creditLog);
        return true;
    }

    public static function getCredit($user_id = 0)
    {
        if (!$user_id) {
            return 400;
        }

        $credit = Model::getFirstData(self::T_CREDIT, ['f_user_id' => $user_id]);
        return $credit;
    }

    public static function getCreditLog($user_id = 0, $page = 1, $limit = 10)
    {
        if (!$user_id) {
            return 400;
        }
        if($page < 1) {
            $page = 1;
        }

        $logs = Model::getData(self::T_CREDIT_LOG, ['f_user_id' => $user_id, 'f_flag'=>1], $page, $limit);
        return $logs;
    }
}
