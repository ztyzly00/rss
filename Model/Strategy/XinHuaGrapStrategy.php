<?php

namespace Model\Strategy;

use Model\Strategy\Sinterface\IGrapStrategy;

/**
 * 新华网抓取策略
 * 
 * @author         zanuck<ztyzly00@126.com> 
 * @since          1.0 
 */
class XinHuaGrapStrategy implements IGrapStrategy {

    /**
     * 抓取信息并填入到属性数组中去
     * @param type $crawler
     * @param type $attributes
     * @return type
     */
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

    /**
     * 返回NextPage的信息
     * @param type $crawler
     * @param type $attributes
     */
    public static function NextPage($crawler, $attributes) {
        $info = $attributes;

        //判断是否存在下一页链接
        if ($crawler->filter('.nextpage')->getNode(0)) {
            $next_flag = 0;
            $next_url = "";
            for ($i = 0; $i < $crawler->filter('.nextpage')->count(); $i++) {
                if ($crawler->filter('.nextpage')->getNode($i)->nodeValue == '下一页') {
                    $next_flag = 1;
                    $next_url = $crawler->filter('.nextpage')->getChild($i)->link()->getUri();
                    $info['link'] = $next_url;
                    break;
                }
            }

            if ($next_flag == 1) {
                $info['pageid'] = $info['pageid'] + 1;
                return $info;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

}
