<?php

namespace Model;

use Goutte\Client;
use Core\MySql\Mysql_Model\XmMysqlObj;

class NewsInfo {
    /**
     * $title;$newsid;$link;$catid;$content;
      $source;$time;$rssid;$pageid;
     * @var type 
     */

    /**
     * 属性
     * @var type 
     */
    public $attributes;

    /**
     * 抓取类
     * @var type 
     */
    public $crawler;

    /**
     * 构造函数
     * @param type $info
     */
    public function __construct($info) {
        $this->attributes = $info;
        if (!isset($info['pageid'])) {
            $this->attributes['pageid'] = 0;
        }
        if (!isset($info['newsid'])) {
            $this->attributes['newsid'] = uniqid();
        }

        $client = new Client();
        $this->crawler = $client->request('GET', $this->attributes['link']);
        $this->grabHtml();
    }

    /**
     * 抓取内容
     */
    public function grabHtml() {

        //是图集
        if ($this->crawler->filter('.bai13')->getNode(0)) {
            //内容信息记录
            $this->attributes['content'] = $this->crawler->filter('.bai13')->eq(1)->html();
            //来源信息记录
            $this->attributes['source'] = $this->crawler->filter('.info')->text();
        }
        //不是图集，是普通的文章模型
        else {
            $this->attributes['source'] = $this->crawler->filter('#source')->text();
            if ($this->crawler->filter('.article')->getNode(0)) {
                $this->attributes['content'] = $this->crawler->filter('.article')->html();
            } elseif ($this->crawler->filter('#content')->getNode(0)) {
                $this->attributes['content'] = $this->crawler->filter('#content')->html();
            }
        }
    }

    /**
     * 打印内容
     */
    public function printInfo() {
        for ($i = 0; $i < count($this->attributes); $i++) {
            print_r($this->attributes[$i] . "\n");
        }
    }

    /**
     * 存储到数据库
     */
    public function saveToDb() {

        if ($this->attributes['content']) {
            $query = "insert into rs_news (";
            foreach ($this->attributes as $key => $value) {
                $query = $query . "`{$key}`,";
            }
            $query = substr($query, 0, -1);
            $query = $query . ") values (";
            foreach ($this->attributes as $key => $value) {
                if (is_int($value)) {
                    $query = $query . "$value,";
                } else {
                    $query = $query . "'$value',";
                }
            }
            $query = substr($query, 0, -1);
            $query = $query . ")";
            $xm_mysql_obj = XmMysqlObj::getInstance();
            $xm_mysql_obj->exec_query($query);
        }
    }

    /**
     * 返回该网页的下一页网页,如果没有返回null
     * @return null
     */
    public function nextPage() {
        $info = $this->attributes;

        if ($this->crawler->filter('.nextpage')->getNode(0)) {
            $next_flag = 0;
            $next_url = "";
            for ($i = 0; $i < $this->crawler->filter('.nextpage')->count(); $i++) {
                if ($this->crawler->filter('.nextpage')->getNode($i)->nodeValue == '下一页') {
                    $next_flag = 1;
                    $next_url = $this->crawler->filter('.nextpage')->getChild($i)->link()->getUri();
                    $info['link'] = $next_url;
                    break;
                }
            }

            if ($next_flag == 1) {
                $info['pageid'] = $info['pageid'] + 1;
                $next_page = new static($info);
                return $next_page;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

}
