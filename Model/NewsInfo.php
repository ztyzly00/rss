<?php

namespace Model;

use Goutte\Client;
use Core\MySql\Mysql_Model\XmMysqlObj;

/**
 * 新闻抓取实例
 * 
 * 包含页面信息以及抓取类库实例
 * @author         zanuck<ztyzly00@126.com> 
 * @since          1.0 
 */
class NewsInfo {

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
     * 数据库句柄
     * @var type 
     */
    public $xm_mysql_obj;

    /**
     * 下页文章属性
     * @var type 
     */
    public $next_page_attributes;

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
        if (!isset($info['nextpage'])) {
            $this->attributes['nextpage'] = 0;
        }
        if (!isset($info['nextdone'])) {
            $this->attributes['nextdone'] = 0;
        }

        /* 初始化抓取类库 */
        try {
            $client = new Client();
            $this->crawler = $client->request('GET', $this->attributes['link']);
        } catch (Exception $e) {
            print_r($e) . "\n";
            exit;
        }

        /* 不使用单例模式，因为需要高度并行 */
        $this->xm_mysql_obj = XmMysqlObj::getInstance(1);

        /* 获取newsinfo的各种信息 */
        $this->getAttribute();
    }

    /**
     * 获取newsinfo的各种信息
     */
    public function getAttribute() {

        /* 获取下一网页属性 */
        $this->getNextPageAttr();

        /* 默认实例化对象就开始抓取内容 */
        $this->grabHtml();
    }

    /**
     * 抓取内容
     */
    public function grabHtml() {
        $strategy_class = $this->getStrategy();
        $this->attributes = call_user_func(array($strategy_class, 'GrapHtml'), $this->crawler, $this->attributes);
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
            $this->xm_mysql_obj->exec_query($query);
        }
    }

    /**
     * 返回该网页的下一页网页,如果没有返回null
     * @return 返回下一个网页的实例对象
     */
    public function nextPage() {

        if ($this->next_page_attributes) {
            $next_page = new static($this->next_page_attributes);
            if ($next_page) {
                $this->setNextDone();
                return $next_page;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * 获取下一网页的属性
     */
    public function getNextPageAttr() {
        $strategy_class = $this->getStrategy();
        $this->next_page_attributes = call_user_func(array($strategy_class, 'NextPage'), $this->crawler, $this->attributes);
        if ($this->next_page_attributes) {
            $this->attributes['nextpage'] = 1;
        } else {
            $this->attributes['nextdone'] = 1;
        }
    }

    /**
     * 设置当前抓取的新闻没有下页，无需抓取。
     * 需要用到Innodb事务，因为高度并发性。
     * 事务隔离级别可防止幻读
     */
    public function setNextDone() {
        $this->xm_mysql_obj->exec_query("SET AUTOCOMMIT=0;BEGIN WORK;");
        $this->xm_mysql_obj->exec_query("select nextdone from rs_news where link='{$this->attributes['link']}' for update");
        $this->xm_mysql_obj->exec_query("update rs_news set nextdone = 1 where link='{$this->attributes['link']}'");
        $this->xm_mysql_obj->exec_query("COMMIT WORK");
    }

    /**
     * 获取策略
     * @return type 返回策略名称    
     */
    public function getStrategy() {
        $query = "select strategy from rs_rss_map where rssid='{$this->attributes['rssid']}'";
        $row = $this->xm_mysql_obj->fetch_array_one($query);
        return "Model\\Strategy\\" . $row['strategy'];
    }

}
