<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
