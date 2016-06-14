<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model\Strategy;

use Model\Strategy\Sinterface\IGrapStrategy;

class XinHuaGrapStrategy implements IGrapStrategy {

    public static function GrapHtml($crawler, $attributes) {
        //是图集
        if ($crawler->filter('.bai13')->getNode(0)) {
            //内容信息记录
            $attributes['content'] = $crawler->filter('.bai13')->eq(1)->html();
            //来源信息记录
            $attributes['source'] = $crawler->filter('.info')->text();
        }
        //不是图集，是普通的文章模型
        else {
            $attributes['source'] = $crawler->filter('#source')->text();
            if ($crawler->filter('.article')->getNode(0)) {
                $attributes['content'] = $crawler->filter('.article')->html();
            } elseif ($crawler->filter('#content')->getNode(0)) {
                $attributes['content'] = $crawler->filter('#content')->html();
            }
        }

        return $attributes;
    }

}
