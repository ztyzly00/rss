<?php

require_once 'vendor/autoload.php';
require_once 'autoload.php';

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
        $this->attributes['link'] = $info['link'];
        $this->attributes['catid'] = $info['catid'];
        $this->attributes['time'] = $info['time'];
        $this->attributes['title'] = $info['title'];
        $this->attributes['rssid'] = $info['rssid'];
        $this->attributes['description'] = $info['description'];
        if (isset($info['pageid'])) {
            $this->attributes['pageid'] = $info['pageid'];
        } else {
            $this->attributes['pageid'] = 0;
        }
        if (isset($info['newsid'])) {
            $this->attributes['newsid'] = $info['newsid'];
        } else {
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
            }
        }
    }

    /**
     * 打印内容
     */
    public function printInfo() {
        print_r($this->attributes['catid'] . "\n");
        print_r($this->attributes['content'] . "\n");
        print_r($this->attributes['link'] . "\n");
        print_r($this->attributes['source'] . "\n");
        print_r($this->attributes['time'] . "\n");
        print_r($this->attributes['title'] . "\n");
        print_r($this->attributes['rssid'] . "\n");
        print_r($this->attributes['pageid'] . "\n");
    }

    /**
     * 存储到数据库
     */
    public function saveToDb() {
        $xm_mysql_obj = XmMysqlObj::getInstance();
        $query = "insert into rs_news (`newsid`,`catid`,`title`,`content`,`pageid`,`source`,`rssid`,`time`,`description`,`basehref`) values "
                . "('{$this->attributes['newsid']}',{$this->attributes['catid']},'{$this->attributes['title']}',"
                . "'{$this->attributes['content']}',{$this->attributes['pageid']},'{$this->attributes['source']}',"
                . "{$this->attributes['rssid']},'{$this->attributes['time']}','{$this->attributes['description']}','{$this->attributes['link']}')";
        $xm_mysql_obj->exec_query($query);
    }

    /**
     * 返回该网页的下一页网页,如果没有返回null
     * @return null
     */
    public function nextPage() {
        $client = new Client();
        $this->crawler = $client->request('GET', $this->attributes['link']);

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
                echo $next_url;
                return $next_page;
            } else {
                return null;
            }
            $this->attributes['pageid'] = $this->attributes['pageid'] + 1;
        } else {
            return null;
        }
    }

}
