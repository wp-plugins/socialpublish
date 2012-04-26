<?php

require_once __SOCIALPUBLISH_ROOT__ . '/domain/http/strategy/ASocialpublishHTTPStrategy.php';
require_once __SOCIALPUBLISH_ROOT__ . '/domain/http/SocialpublishHTTPException.php';

class SocialpublishHTTPSocketStrategy extends ASocialpublishHTTPStrategy
{
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new SocialpublishHTTPSocketStrategy();
        }

        return self::$instance;
    }

    public function isAvailable() {
        return function_exists('fsockopen') &&
               function_exists('parse_url') &&
               function_exists('fgets') &&
               function_exists('fwrite') &&
               function_exists('feof') &&
               function_exists('fclose')
        ;
    }

    protected function send($method, $url, $parameters = null) {
        $c = parse_url($url);

        if ($c['scheme'] !== 'http') {
            return null;
        }

        if ($parameters !== null && is_array($parameters) && sizeof($parameters) > 0) {
            $p = array();
            foreach ($parameters as $name => $value) {
                $p[] = urlencode($name) . "=" . urlencode($value);
            }
            $parameters = join('&', $p);
        } else {
            $parameters = '';
        }


        $crlf = "\r\n";
        $req  = $method . ' '. $c['path'] . ($method === 'GET' ? '?' . $parameters : '') . ' HTTP/1.1' . $crlf;
        $req .= 'Host: '. $c['host'] . $crlf;
        $req .= 'User-Agent: Mozilla/5.0 Firefox/3.6.12' . $crlf;
        $req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf;
        $req .= 'Accept-Language: en-us,en;q=0.5' . $crlf;
        $req .= 'Accept-Encoding: deflate' . $crlf;
        $req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf;
        $req .= 'Connection: Close' . $crlf;

        if ($method === 'POST' && strlen($parameters) > 0) {
            $req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf;
            $req .= 'Content-Length: '. strlen($parameters) . $crlf . $crlf;
            $req .= $parameters;
        } else {
            $req .= $crlf;
        }

        if (!isset($c['port']) || $c['port'] == 0) {
            $port = 80;
        } else {
            $port = $c['port'];
        }

        $fp = @fsockopen($c['host'], $port, $errno, $errstr, 10);

        if ($fp === false) {
            throw new SocialpublishHTTPException();
        }

        fwrite($fp, $req);
        $ret = '';
        while (!feof($fp)) {
            $ret .= fgets($fp, 128);
        }
        fclose($fp);

        return substr($ret, strpos($ret, "\r\n\r\n") + 4);
    }
}

?>