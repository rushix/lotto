<?php

class Lotto {

    private $url = "https://www.njlottery.com/api/v1/draw-games/draws/page?";
    /* Default query params */
    private $params = array(
        'game-names'    => "Pick 3", 
        'date-from'     => "1462086000000", 
        'date-to'       => "1463900400000",
        'status'        => "CLOSED",
        'page'          => "0",
        'size'          => "200"
    );
    private $response_handler = false;
    private $response = array();

    /**
     *
     * @param array $params
     * @return object | null
     *
     */
    function __construct($params = array()) {
        $this->params['game-names'] = isset($params['game-names']) ? $params['game-names'] : $this->params['game-names'];
        $this->params['date-from']  = isset($params['date-from'])  ? $params['date-from']  : $this->params['date-from'];
        $this->params['date-to']    = isset($params['date-to'])    ? $params['date-to']    : $this->params['date-to'];
        $this->params['status']     = isset($params['status'])     ? $params['status']     : $this->params['status'];
        $this->params['page']       = isset($params['page'])       ? $params['page']       : $this->params['page'];
        $this->params['size']       = isset($params['size'])       ? $params['size']       : $this->params['size'];

        $this->response_handler = $this->getResponse($this->url . http_build_query($this->params));
        $this->response = json_decode($this->response_handler, true);
        
        if ($this->checkResponseHandler() && $this->checkResponse(json_last_error())) {
            return $this;
        } else {
            return null;
        }
    }

    /**
     *
     * @param string $url
     * @return string
     *
     */
    private function getResponse($url) {

        $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL,$url);
        
        return curl_exec($ch);

    }

    /**
     *
     * @return bool
     *
     */
    private function checkResponseHandler() {
        if ($this->response_handler == false) {
            throw new Exception("Check your internet connection");
            return false;
        }

        return true;
    }

    /**
     *
     * @return bool
     *
     */
    private function checkResponse($json_last_error) {
        if ($json_last_error !== JSON_ERROR_NONE) {
            throw new Exception("Check given params");
            return false;
        }

        return true;
    }

    /**
     *
     * @param   array | string  $keys
     * @param   integer         $limit
     * @param   integer         $offset
     * @return  array
     *
     */
    public function get($keys = array(), $limit = -1, $offset = 0) {
        $result = array();

        if (!isset($this->response['draws']) || !is_array($this->response['draws']) || count($this->response['draws']) == 0) {
            return $result;
        }
        
        $draws = $this->response['draws'];
        $draws_keys = array_keys($draws[0]);

        $limit = (!is_int($limit) || $limit < 0) ? count($draws) : $limit;
        $offset = (!is_int($offset) || $offset < 0) ? 0 : $offset;
        
        $keys = (is_array($keys) || is_string($keys)) ? $keys : array();

        $correct_keys = array();
        if (is_array($keys)) {
            foreach ($keys as $key) {
                if (in_array($key, $draws_keys)) {
                    $correct_keys[] = $key;
                }
            }
        } else {
            if (in_array($keys, $draws_keys)) {
                $correct_keys = $keys;
            }
        }

        for ($i = 0; $i < count($draws) && $limit > 0; $i++) {
            if ($i < $offset) {
                continue;
            }

            $limit--;

            if (is_array($correct_keys)) {
                $entry = array();
                foreach($correct_keys as $correct_key) {
                    $entry[$correct_key] = $draws[$i][$correct_key];
                }
                $result[] = $entry;
            } else {
                $result[] = $draws[$i][$correct_keys];
            }
        }

        return $result;
    }
}

try {

    $lotto = new Lotto(array('date-from' => "1462086000000", 'date-to' => "1453900400000"));
    print_r($lotto->get('id'));

    /************************ ANOTHER EXAMPLES OF USAGE *************************/
    // $lotto = new Lotto(array('game-names' => "Pick 4", 'size' => "10"));
    // print_r($lotto->get(array('id', 'name', 'results')));
    //
    // $lotto = new Lotto($_REQUEST);
    // print_r($lotto->get(array('id', 'name', 'results'), 10, 3));
    //
    // In the constructor you may specify(or not) diffent params, such as:
    // game-names
    // date-from
    // date-to
    // size
    // status
    // page
    //
    // get() methods params are:
    // 
    // $key -- can be an array or a string
    // format of the returning array depends on type of the '$key' param
    // 
    // $limit
    // $offset
    /****************************************************************************/


} catch (Exception $e) {
    echo 'Exception throwed: ',  $e->getMessage(), "\n";
}

?>
