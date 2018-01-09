<?php

class StatisticsController  extends BaseController{

    public function add(Request $request, Response $response)
    {
        if ('infoflow' == $request->input('token')) {
            // 统计ssp广告数据
            $obj = new stdClass;
            $identify = $request->input('f_app_identify');
            $company = Model::getFirstData('medias', ['f_ssptoken' => $identify], 'f_company_id');
            if ($company) {
                $compay_id                 = $company->f_company_id;
                $obj->f_adminid            = $compay_id;
                $obj->f_app_identify       = $identify;
                $obj->f_media_pos_identify = $request->input('f_media_pos_identify');
                $obj->f_after_cpc_count    = $request->input('f_after_cpc_count');
                $obj->f_after_cpm_count    = $request->input('f_after_cpm_count');
                $obj->f_after_total_price  = $request->input('f_after_total_price');
                $obj->f_date               = $request->input('f_date');
                $obj->f_month              = $request->input('f_month');
                $obj->f_addtime            = $request->input('f_addtime');
                $obj->f_status             = $request->input('f_status');
                if (Model::saveData('after_media_day_statistics', $obj)) {
                    $response->json(['code' => 200, 'msg' => 'success']);
                } else {
                    $response->json(['code' => 400, 'msg' => 'fail']);
                }
            } else {
                $response->json(['code' => 300, 'msg' => '没有对应的渠道']);
            }


        } else {
             $response->json(['code' => 100, 'msg' => '验证失败']);
        }
    }
}



