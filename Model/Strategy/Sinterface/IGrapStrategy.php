<?php

/**
 * 抓取策略接口
 * 
 * @author         zanuck<ztyzly00@126.com> 
 * @since          1.0 
 */

namespace Model\Strategy\Sinterface;

interface IGrapStrategy {

    /**
     * 抓取内容策略
     */
    public static function GrapHtml($crawler, $attributes);

    /**
     * 下一页选取策略
     */
    public static function NextPage($crawler, $attributes);
}
