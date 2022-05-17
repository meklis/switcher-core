<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;
use Monolog\Logger;

class CardList extends C300ModuleAbstract
{
    /**
     * @Inject
     * @var Logger
     */
    protected $logger;

    public function run($params = [])
    {
        $cache = $this->getCache('card_list');
        if($cache) {
            $this->response = $cache;
            return  $this;
        } else {
            $this->logger->notice("Cache by key 'card_list' not found");
        }
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = $this->parseTable(explode("\n", $this->telnet->exec("show card")));
        $types = [];
        foreach ($this->model->getExtra()['card_types'] as $type) {
            $types[$type['name']] = $type;
        }
        foreach ($this->response as $k=>$resp) {
            if(isset($types[$resp['real_type']])) {
                $this->response[$k]['technology'] = $types[$resp['real_type']]['interface_type'];
            } else {
                $this->response[$k]['technology'] = null;
            }
        }
        $this->setCache('card_list', $this->response, 3600);
        return  $this;
    }
    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

}