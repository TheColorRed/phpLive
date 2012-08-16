<?php
/*
 * phpLive PHP Library v1.0.0-alpha
 * http://phplive.org/
 *
 * To see the documentation, visit:
 * http://phplive.org/docs
 *
 * To report/view bugs, visit:
 * http://bugs.phplive.org
 *
 * Copyright 2011, Ryan Naddy
 * GPL Version 3 licenses.
 * http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Date: Thu Jan 06 2012
 */

/*
 * Define some constants
 */
// List of extraction constants
define("EXTRACT_SYMBOL", 1);
define("EXTRACT_NUMBER", 2);
define("EXTRACT_LETTER", 3);
define("EXTRACT_UPPER", 4);
define("EXTRACT_LOWER", 5);
define("EXTRACT_PHONE", 6);
define("EXTRACT_EMAIL", 7);
define("EXTRACT_LINKS", 8);
define("EXTRACT_NUMBER_LETTER", 9);

// List of removal constants
define("REMOVE_SYMBOL", 1);
define("REMOVE_WHITE_SPACE", 2);
define("REMOVE_NUMBER", 3);
define("REMOVE_LETTER", 4);
define("REMOVE_UPPER", 5);
define("REMOVE_LOWER", 6);

// List of validation constants
define("VALID_EMAIL", 1);
define("VALID_DATE", 2);
define("VALID_TIME", 3);
define("VALID_DATETIME", 4);
define("VALID_URL", 5);

// List of return type constants
define("RETURN_INT", 1);
define("RETURN_ARRAY", 2);
define("RETURN_OBJECT", 3);
define("RETURN_STRING", 4);
define("RETURN_BOOL", 5);

// List of data constants
define("DATA_HTML", 1);
define("DATA_CLEAN", 2);

// List of input types
define("INPUT_STRING", 1);
define("INPUT_FILE", 2);

define("MAIL_HTML", 1);
define("MAIL_ATTACHMENT", 2);
define("MAIL_PLAIN", 3);

define("DOWNLOAD_FILE", 1);
define("DOWNLOAD_STRING", 2);

define("PHP_TMPFILE", 1);

define("ZIP_NEW", 1);

define("HIGHLIGHT_PHP", 1);
define("HIGHLIGHT_HTML", 2);
define("HIGHLIGHT_CSS", 3);

define("IMAGE_JPG", 1);
define("IMAGE_PNG", 2);
define("IMAGE_GIF", 3);
// Begin phpLive Class
class phpLive{
    // Protected Properties
    protected $phpLiveDomain = 'http://www.phplive.org';
    protected $version       = "1.0.0-alpha";
    protected $location      = '';
    protected $images        = array("jpg", "jpeg", "gif", "png");
    protected $conn_id       = 0;
    protected $exit          = array();
    protected $threads       = array();
    protected $thread_count  = 0;
    protected $errors        = array();
    protected $filename      = '';
    protected $filebasename  = "";
    protected $newFilename   = '';
    protected $phones        = array("lg-", "htc", "sie", "mot-", "iphone", "android", "webos", "blackberry", "ipod", "nokia", "samsung", "sonyericsson");
    protected $sockets       = array();
    protected $sockId        = 0;
    protected $clients       = array();
    protected $zip           = array();
    protected $zipId         = 0;
    protected $memorySize    = 2;
    protected $tmpFile       = "";

    // Private Read-Only Properties
    private $url, $ch, $links, $cleanData, $info, $title, $endingUrl, $httpCode, $loadTime;
    private $processing      = false, $urlQuery;
    private $extension       = array();

    // Public Properties
    public $colors           = array("#ffffff", "#eeeeee");
    public $coreLoaded       = false;
    public $thumbDir;
    public $content;
    public $db               = array();
    public $dbHostname, $dbUsername, $dbPassword, $dbDatabase, $dbPort, $dbResult, $dbRow, $dbQueries = 0;
    public $port             = 80;
    public $host             = 'localhost';
    public $list             = array();
    public $post             = array();
    public $functionName     = null;
    public $quickString      = "";

    public function __construct(){
        $this->location = dirname(__FILE__);
        parse_str($this->qString(), $this->urlQuery);
    }

    public function __get($name){
        switch($name){
            case 'links': $ret = $this->links;
                break;
            case 'location':
                if(empty($this->location))
                    $this->location = dirname(__FILE__);
                $ret = $this->location;
                break;
            case 'data': $ret = $this->content;
                break;
            case 'url': $ret = $this->url;
                break;
            case 'info': $ret = $this->info;
                break;
            case 'endingUrl': $ret = $this->endingUrl;
                break;
            case 'httpCode': $ret = $this->httpCode;
                break;
            case 'loadTime': $ret = $this->loadTime;
                break;
            case 'cleanData': $ret = $this->cleanData;
                break;
            case 'title': $ret = $this->title;
                break;
            case 'exit': $ret = $this->exit;
                break;
            case 'processing': $this->poll();
                $ret = $this->processing;
                break;
            case 'errors': $ret = $this->errors;
                break;
            case 'urlQuery': $ret = $this->urlQuery;
                break;
            default:
                if(array_key_exists($name, $this->extension)){
                    $ret = $this->extension[$name];
                }else{
                    $ret = false;
                }
                break;
        }
        return $ret;
    }

    public function __set($name, $value){
        $this->extension[$name] = $value;
    }

    public function __call($name, $args){
        if(isset($this->$name) && is_callable($this->$name)){
            return call_user_func_array($this->$name, $args);
        }
        if(isset($this->extension[$name]) && is_callable($this->extension[$name])){
            return call_user_func_array($this->extension[$name], $args);
        }
        throw new BadFunctionCallException(sprintf('Undefined function %s.', $name));
    }

    public function __tostring(){
        $opt = "";
        switch($this->functionName){
            case "each":
                if(is_array($this->list))
                    $opt = implode("", $this->list);
                else
                    $opt = $opt = $this->quickString;
                break;
            case "qRemove":
            case "qAdd":
                $opt = http_build_query($this->urlQuery);
                parse_str($this->qString(), $this->urlQuery);
                break;
            default:
                $opt = $this->quickString;
                break;
        }
        return (string)$opt;
    }

    public function toString(){
        return $this->__tostring();
    }

    public function toLower($string = null){
        if(is_string($string))
            $this->quickString = $string;
        $this->quickString = strtolower($this->quickString);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function toUpper($string = null){
        if(is_string($string))
            $this->quickString = $string;
        $this->quickString = strtoupper($this->quickString);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function toInt(){
        return (int)$this->__tostring();
    }

    public function toBool(){
        return (bool)$this->__tostring();
    }

    public function string(&$string = null){
        if($string != null)
            $string = $string;
        return $this->quickString;
    }

    public function version(){
        $this->functionName = __FUNCTION__;
        echo $this->version;
    }

    public function getCalledClass(){
        if(function_exists('get_called_class')){
            return get_called_class();
        }
        $t = debug_backtrace();
        $t = $t[0];
        $this->functionName = __FUNCTION__;
        if(isset($t['object']) && $t['object'] instanceof $t['class'])
            return get_class($t['object']);
        return false;
    }

    public function getCalledPlugin(){
        $this->getCalledClass();
        $this->allPluginSettings();
        //var_dump(get_class());
        print_r(debug_backtrace());
    }

    public function numMethods($class){
        $this->quickString = count(get_class_methods($class));
        return $this;
    }

    public function loadPlugin($class, $info){
        $info = (object)$info;
        $file = $this->location."/plugins/".$info->root."/".$info->fileName;
        if(is_file($file)){
            require_once $file;
            $instance = (string)$info->instanceName;
            $this->$instance = new $class();
            $this->functionName = __FUNCTION__;
            $this->extension[$instance] = $this->$instance;
            return $this->$instance;
        }
        $this->functionName = __FUNCTION__;
        return false;
    }

    public function loadPlugins($loadPlugins = null){
        if(is_string($loadPlugins)){
            $loadPlugins = explode(",", $loadPlugins);
        }
        if(!is_array($loadPlugins) && $loadPlugins != null){
            return false;
        }
        $ini = $this->allPluginSettings();
        if($ini){
            if($loadPlugins == null){
                foreach($ini as $sectionClass => $section){
                    $this->loadPlugin($sectionClass, $section);
                }
            }else{

                foreach($loadPlugins as $sectionClass){
                    $section = $ini[trim($sectionClass)];
                    $this->loadPlugin($sectionClass, $section);
                }
            }
            $this->functionName = __FUNCTION__;
            return true;
        }
        $this->functionName = __FUNCTION__;
        return false;
    }

    public function allPluginSettings(){
        $this->functionName = __FUNCTION__;
        if(empty($this->location))
            $this->location = dirname(__FILE__);
        $file = $this->location."/plugins/plugins.ini";
        if(is_file($file))
            return parse_ini_file($file, true);
        else
            return false;
    }

    public function pluginSettings($class = null, $return_object = true){
        $ini   = $this->allPluginSettings();
        if($class == null)
            $class = $this->getCalledClass();
        $this->functionName = __FUNCTION__;
        if($return_object)
            return (object)$ini[$class];
        else
            return $ini[$class];
    }

    /*public function extend($callback){
        $object = (object)$callback;

        var_dump(get_class_vars($object));
    }*/

    /*
     * HTTP Methods
     */
    /*
     * get_http gets a page from the web, and saves information into its parameters.
     * $this->endingUrl the final url when the page was loaded (This can be different from your start url due to http redirects)
     * $this->httpCode is the code in wich it returned (200, 400, 500, etc)
     * $this->loadTime is the time it took for the page to load fully
     * $this->title the title of the page
     * $this->content the html content of the page
     * $this->cleanData the cleaned up version of content
     * $this->links an array of links (other than javascript links) that were found on the page
     */

    public function getHttp($url = null, $other_params = null){
        if($url == null){
            if(filter_var($this->quickString, FILTER_VALIDATE_URL)){
                $this->url = $this->quickString;
            }
        }else{
            $this->url = $url;
        }
        $url = $this->url;
        $this->filebasename = basename($url);
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        if(!empty($this->post)){
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->post);
        }
        if(isset($_SERVER['HTTP_USER_AGENT']))
            curl_setopt($this->ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        else
            curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatable; phpLiveBot/$this->version; +$this->phpLiveDomain)");
        if(is_array($other_params)){
            foreach($other_params as $key => $val){
                curl_setopt($this->ch, $key, $val);
            }
        }
        $this->content = curl_exec($this->ch);
        $this->info = (object)curl_getinfo($this->ch);
        $this->endingUrl = $this->info->url;
        $this->httpCode = $this->info->http_code;
        $this->loadTime = $this->info->total_time;
        curl_close($this->ch);
        $this->quickString = $this->content;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * String methods
     */
    /*
     * returns a cleaned up version of $this->content, meaning:
     * - Removes Javascript
     * - Removes comments
     * - Removes CSS
     * - Removes HTML
     * Finally, a clean version of an html page is returned
     */

    public function getCleanData($data = null){
        if($data == null)
            $data  = $this->quickString;
        $clean = preg_replace("/\<(script|style).*\>.*\<\/(script|style)\>/isU", " ", $data);
        $clean = preg_replace("/\<title.*\>.*\<\/title\>/isU", " ", $clean);
        $clean = preg_replace("/\<\!--.*\--\>/isU", " ", $clean);
        $clean = preg_replace("/\</isU", " <", $clean);
        $clean = strip_tags($clean);
        $clean = preg_replace("/(\r|\n|\t)/", " ", $clean);
        $clean = preg_replace("/\s\s+/", " ", $clean);
        $clean = $this->convertSmart($clean);
        $clean = trim($clean);
        $this->cleanData = $clean;
        $this->quickString = $clean;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * Gets the page title of the html that is saved in $this->content
     * if no title is found, we return false
     */

    public function getTitle($data = null){
        if($data == null){
            $data = $this->content;
        }
        preg_match("/\<title.*\>(.*)\<\/title\>/isU", $data, $matches);
        if(isset($matches[1]))
            $this->title = $matches[1];
        else
            return false;
        $this->list = $this->title;
        $this->quickString = $this->title;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * get_urls will find all link tags and return their href attribues
     * if $data is a string, the new internal data value will be set to that, and this method will search that
     */

    public function getLinks($data = null){
        if($data == null){
            $data = $this->content;
        }
        preg_match_all("/\<a.+?href=(\"|')(?!javascript:|#)(.+?)(\"|')/i", $data, $matches);
        $this->links = $matches[2];
        $this->list = $this->links;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * mkstr (Make String) will take a formatted string and make a nice string out of it.
     * For example:
     * echo mkstr("%Y-%m-%d_rand(10).%x"); // prints 2011-07-13_ceffe67048.1310587480
     * The above out put looks like this:
     * Year-Month-Day_Random10.UnixTime
     */

    public function mkstr($format){
        $name = preg_replace("/%x/", time(), $format);
        $name = preg_replace("/(%([a-zA-Z])){1}/e", 'date("$2")', $name);
        if(preg_match("/rand\(([0-9]+)\)/", $name, $matches)){
            $total   = $matches[1];
            $str     = "";
            $letters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
            for($i       = 0; $i < $total; $i++){
                $pos  = mt_rand(0, strlen($letters));
                $str .= $letters[$pos];
            }
            $name = preg_replace("/rand\(([0-9]+)\)/", $str, $name);
        }
        if(preg_match("/\/(.+)$/", $name, $matches)){
            $type = $matches[1];
            if(in_array($type, hash_algos())){
                eval('$name = hash("'.$type.'", $name);');
            }
        }
        $this->quickString = $name;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function hash($content = null, $type = 'md5'){
        $this->functionName = __FUNCTION__;
        if($content == null)
            $content = $this->quickString;
        if(in_array($type, hash_algos())){
            $this->quickString = hash($type, $content);
            return $this;
        }
        return false;
    }

    /*
     * get_between gets values between a starting value and ending value.
     * $data_type defines the type of data that will be passed to it (DATA_HTML or DATA_CLEAN)
     * $include_start_end if it is set to false, $start and $end will NOT be in the returned result, if set to ture, it will
     * $htmlFormat if set to false the returned result will NOT be formatted html, but html entities otherwise it will return as html
     */

    public function getBetween($start, $end, $data = null, $dataType = DATA_HTML, $include_start_end = false, $htmlFormat = false){
        $start = preg_quote($start, "/");
        $end   = preg_quote($end, "/");
        if($data == null){
            if($dataType == DATA_CLEAN)
                $data   = $this->cleanData;
            elseif($dataType == DATA_HTML)
                $data   = $this->content;
            else
                return false;
        }
        $good   = (bool)preg_match_all($search = "/$start(.+)$end/uisU", $data, $matches);
        $ret    = array();
        if($good){
            if(!$htmlFormat){
                if($include_start_end){
                    foreach($matches[0] as $start_end){
                        $ret[] = htmlentities($start_end);
                    }
                }else{
                    foreach($matches[1] as $no_start_end){
                        $ret[] = htmlentities($no_start_end);
                    }
                }
            }else{
                if(!$include_start_end){
                    foreach($matches[0] as $start_end){
                        $ret[] = $start_end;
                    }
                }else{
                    foreach($matches[1] as $no_start_end){
                        $ret[] = $no_start_end;
                    }
                }
            }
            $this->list = $ret;
            $this->functionName = __FUNCTION__;
            return $this;
        }
        return false;
    }

    public function implode($glue = " ", $array = null){
        if($array == null)
            $array = $this->list;
        if(!is_array($array))
            return false;
        $this->quickString = implode($glue, $array);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function maxlen($length, $difference = 3, $end = "...", $string = null){
        if($string == null)
            $string   = $this->quickString;
        $length   = (int)$length;
        $words    = explode(" ", $string);
        $numwords = count($words);
        $diff     = $numwords - $length;
        $i        = 0;
        $new      = array();
        while($i < $length){
            $new[] = $words[$i];
            $i++;
        }
        if($diff <= $difference){
            $c = 1;
            while($c <= $difference){
                $new[] = $words[$i + $c];
                $c++;
            }
            $length += $difference;
        }
        $this->quickString = implode(" ", $new);
        if($length < $numwords)
            $this->quickString .= $end;
        return $this;
    }

    public function spToTab($string = null, $spaces = 4){
        if($string == null)
            $string = $this->quickString;
        $this->quickString = preg_replace("/(&nbsp;){".$spaces."}| {".$spaces."}/", "\t", $string);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function tabToSp($string = null, $spaces = 4){
        if($string == null)
            $string = $this->quickString;
        $this->quickString = preg_replace("/\t/", str_repeat("&nbsp;", $spaces), $string);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function lineCount($string = null, $input = INPUT_STRING){
        if($input == INPUT_STRING){
            if($string == null)
                $string = $this->quickString;
        }
        if($input == INPUT_FILE)
            $string = file_get_contents($string);
        $this->quickString = count(explode("\n", $string));
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function highlight($content = null, $highlight = HIGHLIGHT_PHP, $input = INPUT_STRING){
        if($content == null)
            $content = $this->quickString;
        $this->quickString = "";
        switch($highlight){
            case HIGHLIGHT_PHP:
                if($input == INPUT_STRING){
                    $this->quickString = highlight_string($content, true);
                }elseif($input == INPUT_FILE){
                    if(is_file($content))
                        $this->quickString = highlight_file($content, true);
                    else
                        return false;
                }
                break;
            case HIGHLIGHT_HTML:
                if($input == INPUT_FILE){
                    $content   = file_get_contents($content);
                }
                $iscomment = false;
                $tag       = "#0000ff";
                $att       = "#ff0000";
                $val       = "#8000ff";
                $com       = "#34803a";
                $doc       = "#bf9221";
                $tmpStr    = "";
                $sp        = preg_split('/(<!--.*?-->|<style.*?>.*?<\/style>)/s', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
                foreach($sp as $split){
                    $split = htmlentities($split, ENT_QUOTES, "UTF-8", false);
                    if(preg_match("/&lt;!--/i", $split)){
                        $tmpStr .= '<span style="color:'.$com.';font-style:italic;">'.$split.'</span>';
                    }elseif(preg_match("/&lt;style/i", $split)){
                        //print_r($split);exit;
                        $spl = preg_split("/(&lt;style.*?&gt;|&lt;\/style&gt;)/i", $split, -1, PREG_SPLIT_DELIM_CAPTURE);
                        //print_r($spl);
                        $tmpStr .= preg_replace(array(
                            '~(\s[a-z].*?=)~',
                            '~(&lt;([a-z?]|!DOCTYPE).*?&gt;)~'
                                ), array(
                            '<span style="color:'.$att.';">$1</span>',
                            '<span style="color:'.$tag.';">$1</span>'
                                ), $spl[1]);
                        $tmpStr .= $this->highlight($spl[2], HIGHLIGHT_CSS);
                        $tmpStr .= preg_replace("~(&lt;/[a-zA-Z].*?&gt;)~", '<span style="color:'.$tag.';">$1</span>', $spl[3]);
                    }else{
                        $find = array(
                            '~(\s[a-z].*?=)~', // Highlight the attributes
                            '~(&quot;[a-zA-Z0-9\/].*?&quot;)~', // Highlight the values
                            '~(&lt;([a-z?]|!DOCTYPE).*?&gt;)~', // Highlight the beginning of the opening tag
                            '~(&lt;/[a-zA-Z].*?&gt;)~', // Highlight the closing tag
                            '~(&amp;.*?;)~', // Stylize HTML entities
                            '~(&lt;!DOCTYPE.*?&gt;)~', // DOCTYPE
                        );
                        $replace = array(
                            '<span style="color:'.$att.';">$1</span>',
                            '<span style="color:'.$val.';">$1</span>',
                            '<span style="color:'.$tag.';">$1</span>',
                            '<span style="color:'.$tag.';">$1</span>',
                            '<span style="font-style:italic;">$1</span>',
                            '<span style="color:'.$doc.';">$1</span>',
                        );
                        $tmpStr .= preg_replace($find, $replace, $split);
                    }
                    $iscomment = !$iscomment;
                }
                $this->quickString = $tmpStr;
                break;
            case HIGHLIGHT_CSS:
                if($input == INPUT_FILE)
                    $this->quickString = file_get_contents($content);
                else
                    $this->quickString = $content;
                $this->highlightCSS();
                break;
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function highlightCSS($css = null, $pre = false){
        if($css == null)
            $css    = $this->quickString;
        $this->functionName = __FUNCTION__;
        $tokens = array();
        $len        = strlen($css);
        $i          = 0;
        $state      = 'selector';
        $prevState  = "";
        $tokenValue = '';
        $commenting = false;
        $isvalue    = false;
        $isstring   = false;
        $openStr    = "";
        while($i < $len){
            switch($css[$i]){
                case '{':
                    if(!$commenting){
                        $tokens[] = array('type'    => $state, 'value'   => $tokenValue);
                        $tokens[] = array('type'      => 'ruleset-begin', 'value'     => '{');
                        $state      = 'ruleset';
                        $tokenValue = '';
                    }else{
                        $tokenValue .= $css[$i];
                    }
                    break;
                case '}':
                    if(!$commenting){
                        $tokens[] = array('type'    => $state, 'value'   => $tokenValue);
                        $tokens[] = array('type'      => 'ruleset-end', 'value'     => '}');
                        $state      = 'selector';
                        $tokenValue = '';
                    }else{
                        $tokenValue .= $css[$i];
                    }
                    break;
                default:
                    if($css[$i] == ":" && !$commenting && $state == "ruleset"){
                        $isvalue   = true;
                        $prevState = $state;
                        $tokenValue .= $css[$i];
                        $tokens[]  = array('type'      => "ruleset", 'value'     => $tokenValue);
                        $state      = "value";
                        $tokenValue = "";
                        $i++;
                    }
                    if(isset($css[$i + 1]) && $css[$i].$css[$i + 1] == "/*" && !$commenting){
                        $commenting = true;
                        $prevState  = $state;
                        $state      = "comment";
                    }
                    if(($css[$i] == "'" || $css[$i] == '"') && !$commenting){
                        if(!$isstring){
                            $isstring = true;
                            $openStr  = $css[$i];
                            $tokens[] = array('type'      => $state, 'value'     => $tokenValue);
                            $prevState  = $state;
                            $state      = "string";
                            $tokenValue = "";
                        }else{
                            if($css[$i] == $openStr){
                                $isstring = false;
                                $openStr  = "";
                                $tokenValue .= $css[$i];
                                $tokens[] = array('type'      => "string", 'value'     => $tokenValue);
                                $state      = $prevState;
                                $tokenValue = "";
                                $i++;
                            }
                        }
                    }
                    if(isset($css[$i + 1]) && $css[$i].$css[$i + 1] == "*/" && $commenting){
                        $commenting = false;
                        $tokens[]   = array('type'      => $state, 'value'     => $tokenValue."*/");
                        $state      = $prevState;
                        $tokenValue = "";
                        $i++;
                    }else{
                        if($state == "value" && $css[$i] == ";" && $isvalue){
                            $isvalue  = false;
                            $tokens[] = array('type'    => $state, 'value'   => $tokenValue);
                            $tokens[] = array('type'      => "ruleset", 'value'     => ";");
                            $tokenValue = "";
                            $state      = "ruleset";
                        }else{
                            $tokenValue .= $css[$i];
                        }
                    }
            }
            $i++;
        }
        if(!empty($tokenValue)){
            $tokens[] = array('type'  => $state, 'value' => $tokenValue);
        }
        $this->quickString = "";
        $styles = array(
            'selector'      => 'font-weight: bold;color: #007c00',
            'ruleset'       => 'color: #0000ff;',
            'ruleset-begin' => 'orange',
            'ruleset-end'   => 'orange',
            'comment'       => 'color: #999999;font-style: italic;',
            'value'         => 'color: #000000;',
            'string'        => 'color: #ce7b00;'
        );
        if((bool)$pre)
            $this->quickString .= "<pre>";
        $this->quickString .= "<span style=\"color: #000000;\">";
        foreach($tokens as $tok){
            $style = $styles[$tok['type']];
            $this->quickString .= '<span style="'.$style.'">'.$tok['value'].'</span>';
        }
        $this->quickString .= "</span>";
        if((bool)$pre)
            $this->quickString .= "</pre>";
        return $this;
    }

    public function commonWords($string = null, $max = -1, $min_len = 4){
        if($string == null)
            $string  = $this->quickString;
        $string  = $this->getCleanData()->remove(null, REMOVE_SYMBOL)->remove(null, REMOVE_NUMBER)->toString();
        $words   = explode(" ", $string);
        $wordlst = array();
        foreach($words as $word){
            if(strlen($word) >= $min_len){
                if(!key_exists($word, $wordlst)){
                    $wordlst[$word] = 1;
                }else{
                    $wordlst[$word] += 1;
                }
            }
        }
        arsort($wordlst);
        if($max > -1)
            $wordlst = array_slice($wordlst, 0, $max);
        $this->list = $wordlst;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function unixFormat($string = null){
        if($string == null)
            $string = $this->quickString;
        $this->quickString = preg_replace("/\r\n/", "\n", $string);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * File Methods
     */

    public function open($filename, $mode = 'rb'){
        $this->close();
        $this->filename = $filename;
        $this->handle = fopen("$filename", $mode);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function close(){
        if(is_resource($this->handle)){
            fclose($this->handle);
            $this->handle = null;
            $this->functionName = __FUNCTION__;
            return $this;
        }
        return false;
    }

    public function read($filename){
        $this->open($filename);
        $this->quickString = fread($this->handle, filesize($filename));
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function save($filename, $content = '', $overwrite = true){
        $this->open($filename, 'wb');
        if($content == PHP_TMPFILE){
            $this->content = file_get_contents($this->tmpFile);
        }else{
            if(!empty($content))
                $this->content = $content;
        }
        if(!$overwrite){
            while(is_file($filename)){
                $pi       = (object)pathinfo($filename);
                $file     = $pi->filename;
                $total    = count(glob($pi->dirname."/".$file."*"));
                $ext      = $pi->extension;
                $filename = $pi->dirname."/".$file."_".$total.".".$ext;
                $this->newFilename = $filename;
            }
            $this->open($filename, 'wb');
        }else{
            $this->open($filename, 'wb');
        }
        $ret = fwrite($this->handle, $this->content);
        if($ret === false)
            return false;
        $this->functionName = __FUNCTION__;
        $this->quickString = $this->content;
        return $this;
    }

    public function append($filename = null, $content = ''){
        if(is_string($filename)){
            $this->filename = $filename;
        }
        $this->open($this->filename, 'ab');
        if(!empty($content)){
            $this->content = $content;
        }
        $ret = fwrite($this->handle, $this->content);
        if($ret === false)
            return false;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * duplicate takes a file and duplicates it and appends a number to it.
     * for example apple.txt will be duplicated as apple_1.txt if duplicated again, it will be apple_2.txt and so on
     * if a filename is provided, the newly duplicated file will become the new working file
     */

    public function duplicate($filename = null){
        if(is_string($filename)){
            $this->filename = $filename;
        }
        $this->read($this->filename);
        $ret = $this->save($this->filename, $this->content, false);
        $this->functionName = __FUNCTION__;
        return $ret;
    }

    /*
     * This takes a file and sets it to an empty file, removing all content from the file.
     * by default it works with the current file opend ($this->open(), $this->save(), etc.)
     * if a filename is provide, it will truncate that file and this will become the new working file
     */

    public function truncate($filename = null){
        if(is_string($filename)){
            $this->filename = $filename;
        }
        $ret = $this->save($this->filename, "");
        $this->functionName = __FUNCTION__;
        return $ret;
    }

    /*
     * delete deletes a file from the file system
     * by default, it will delete the file specified by the file methods ($this->open(), $this->save(), etc.)
     * if a filename string is provided, it will delete that file
     */

    public function delete($filename = null){
        if(is_string($filename)){
            $this->filename = $filename;
        }
        if(unlink($this->location."/".$this->filename)){
            $this->functionName = __FUNCTION__;
            return $this;
        }
        return false;
    }

    /*
     * info gets file info about a file, such as:
     * - date created
     * - date modified
     * - file size ($this->file_size())
     * - unix filesize
     *
     * if the file is a jpg, gif, png it will also display image information, such as:
     * - image width
     * - image height
     * - hypotenuse length
     * - mime type
     */

    public function info($filename = null){
        if(is_string($filename)){
            $this->filename = $filename;
        }
        $pi   = (object)pathinfo($filename);
        $info = array();
        $info['created']   = date("Y-m-d H:i:s", filectime($this->filename));
        $info['modified']  = date("Y-m-d H:i:s", filemtime($this->filename));
        $info['filesize']  = $this->fileSize($this->filename);
        $info['ufilesize'] = filesize($this->filename);
        $info['extension'] = $pi->extension;
        $info['filename']  = $pi->filename;
        $info['dirname']   = $pi->dirname;
        $info['basename']  = $pi->basename;
        $img               = getimagesize($this->filename);
        if($img){
            $a                           = $info['image']['width']      = $img[0];
            $b                           = $info['image']['height']     = $img[1];
            $hypoth                      = hypot($a, $b);
            $info['image']['hypotenuse'] = $hypoth;
            $info['image']['mime']       = $img['mime'];
            $info['image']               = (object)$info['image'];
        }
        $this->functionName = __FUNCTION__;
        return (object)$info;
    }

    /*
     * gets a file's size showing either: GB, MB, KB or bytes appended to the files size.
     * By setting $string to false, GB, MB, KB or bytes will be chopped off the end of the string and a float will be returned
     * By setting $precision, you tell it how many places after the decimal you want $percision of 2 would show something like:
     * 349.58KB or 349.58
     */

    public function fileSize($path, $string = true, $precision = 2){
        $size = filesize($path);
        if($size >= 1073741824){
            $fileSize = round($size / 1024 / 1024 / 1024, $precision)."GB";
        }elseif($size >= 1048576){
            $fileSize = round($size / 1024 / 1024, $precision)."MB";
        }elseif($size >= 1024){
            $fileSize = round($size / 1024, $precision)."KB";
        }else{
            $fileSize = $size.' bytes';
        }
        $this->functionName = __FUNCTION__;
        if($string)
            return $fileSize;
        else
            return (float)$fileSize;
    }

    /*
     * rowColor allows you to alternate between 2 row colors
     * without setting any parameters the method goes from color A to color B.
     * Setting $nextColor to false, it will rest at the current color until true is passed back to $nextColor
     * Setting $reset to ture, will force the color to reset back to the first color
     */

    public function rowColor($nextColor = true, $reset = false){
        static $_i_row_count = -1;
        if($reset)
            $_i_row_count = -1;
        if($nextColor)
            $_i_row_count++;
        $ret          = ($_i_row_count % 2) ? $this->colors[0] : $this->colors[1];
        $this->functionName = __FUNCTION__;
        return $ret;
    }

    public function dumpOpt($string, $return = false){
        $find = array("\n", "\0", "\t", "\r");
        $replace = array("\n".'\n', "\0".'\0', "\t".'\t', "\r".'\r');
        $string = str_replace($find, $replace, $string);
        $this->functionName = __FUNCTION__;
        if($return)
            return $string;
        else
            echo $string;
    }

    /*
     * Image GD Methods
     */

    public function imageResize($save_name, $filename = null, $thumbWidth = 200, $quality = 100){
        if($filename === null){
            $image  = imagecreatefromstring($this->quickString);
            $width  = imagesx($image);
            $height = imagesy($image);
            $mime   = "png";
        }else{
            $path   = (object)pathinfo($filename);
            $info   = getimagesize($filename);
            $width  = $info[0];
            $height = $info[1];
            $mime   = preg_replace("/^.+?\//", "", $info['mime']);
            if(function_exists('imagecreatefrom'.$mime)){
                eval('$image = imagecreatefrom'.$mime.'($filename);');
            }else{
                return $this;
            }
        }
        if(in_array($path->extension, $this->images) || $filename === null){
            if($image != null){
                $save_name    = basename($save_name);
                $thumbQuality = $quality;
                $thumbHeight  = $height * ($thumbWidth / $width);
                $thumb        = imagecreatetruecolor($thumbWidth, $thumbHeight);
                imagecopyresampled($thumb, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                eval('$created = image'.$mime.'($thumb, "$this->thumbDir/$save_name"'.(($mime == 'jpeg') ? ', $thumbQuality' : '').');');
                if($created){
                    $this->functionName = __FUNCTION__;
                    return $this;
                }
            }
        }
        return $this;
    }

    public function imageTo($type, $filename, $new_filename = null){
        if(!is_file($filename))
            return $this;
        $image        = file_get_contents($filename);
        $img          = imagecreatefromstring($image);
        if($new_filename == null)
            $pi           = (object)pathinfo($filename);
        else
            $pi           = (object)pathinfo($new_filename);
        $new_filename = $pi->dirname."/".$pi->filename;
        switch($type){
            case IMAGE_PNG:
                imagealphablending($img, false);
                imagesavealpha($img, true);
                imagepng($img, $new_filename.".png", 9);
                break;
            case IMAGE_JPG:
                imagejpeg($img, $new_filename.".jpg", 100);
                break;
            case IMAGE_GIF:
                $colorTransparent = imagecolortransparent($im);
                imagefill($img, 0, 0, $colorTransparent);
                imagecolortransparent($img, $colorTransparent);
                imagegif($img, $new_filename.".gif");
                break;
        }
        imagedestroy($img);
        return $this;
    }

    /*
     * MySQL Database Methods
     *
     * The MySQL methods use the MySQLi library, so in order to use this you need
     * MySQLi compiled with your php version. Many web hosts have this installed already
     * if you are not using a web host, you will have to compile php with MySQLi.
     *
     * The MySQL methods will automatically connect to to the first database connection
     * in the list if one has not been given. If you call mysql_query before opening a
     * connection, the methods will also create a connection to the first database connection
     * in the list. If no connections have been established (no username/password/database/host)
     * are specified, the methods will not connect, and no queries can be made.
     */

    public function dbConnect($host = null, $username = null, $password = null, $database = null, $port = null){
        $conn_id = $this->conn_id;
        if(is_array($host)){
            $username = $host["username"];
            $password = $host["password"];
            $database = $host["database"];
            $port     = $host["port"];
            $host     = $host["host"];
        }elseif($host == null && $username == null && $password == null && $database == null){
            $host     = $this->dbHostname;
            $username = $this->dbUsername;
            $password = $this->dbPassword;
            $database = $this->dbDatabase;
        }
        if(function_exists("mysqli_connect")){
            if(!empty($port))
                $this->db[$conn_id] = @mysqli_connect("$host:$port", $username, $password);
            else
                $this->db[$conn_id] = @mysqli_connect($host, $username, $password);
        }else{
            if(!empty($port))
                $this->db[$conn_id] = @mysql_connect("$host:$port", $username, $password);
            else
                $this->db[$conn_id] = @mysql_connect($host, $username, $password);
        }
        if(!$this->db[$conn_id]){
            $this->setError('001', "Could not connect to MySQL host: $host using $username.");
        }
        $this->conn_id++;
        if(function_exists("mysqli_select_db")){
            $db_sel = @mysqli_select_db($this->db[$conn_id], $database);
        }else{
            $db_sel = @mysql_select_db($database, $this->db[$conn_id]);
        }
        if(!$db_sel){
            $this->setError('002', "Could not connect to MySQL database: $database.");
        }
        $this->functionName = __FUNCTION__;
        return $conn_id;
    }

    public function dbReset($position = 0, $connection_id = 0){
        if(isset($this->db[$connection_id]))
            $this->db[$connection_id];
        else
            return $this;
        if(function_exists("mysqli_data_seek"))
            mysqli_data_seek($this->dbResult[$connection_id], $position);
        else
            mysql_data_seek($position, $this->dbResult[$connection_id]);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function dbPosition($connection_id = 0){
        $num_rows = $this->dbNumRows(null, $connection_id);
        $i        = 0;
        while($result   = $this->dbRow(null, $connection_id)){
            $i++;
        }
        $pointer_position = $num_rows - $i;
        //Return pointer to original position
        if($pointer_position < $num_rows){
            $p = $pointer_position - 1;
            if($p < 0)
                $p = 0;
            $this->dbReset((int)$p, $connection_id);
            $this->dbRow = $this->dbRow(null, $connection_id);
        }
        $this->functionName = __FUNCTION__;
        return (int)($pointer_position - 1);
    }

    public function dbQuery($queries, $connection_id = 0){
        if(empty($this->db)){
            $this->dbConnect();
        }
        if(is_string($queries)){
            $query   = $queries;
            $queries = array();
            $queries[] = $query;
        }
        if(is_array($queries)){
            foreach($queries as $query){
                if(!empty($query)){
                    if(isset($this->db[$connection_id]))
                        $connection = $this->db[$connection_id];
                    else
                        return $this;
                    if(function_exists("mysqli_query")){
                        if(!($this->dbResult[$connection_id] = mysqli_query($connection, $query))){
                            $this->setError('003', mysqli_error($connection));
                            return $this;
                        }
                    }else{
                        if(!($this->dbResult[$connection_id] = mysql_query($query, $connection))){
                            $this->setError('003', mysql_error($connection));
                            return $this;
                        }
                    }
                }
            }
        }
        $this->dbQueries++;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function dbInsertId($connection_id = 0){
        $connection_id = (int)$connection_id;
        if(isset($this->db[$connection_id]))
            $connection    = $this->db[$connection_id];
        else
            return $this;
        $this->functionName = __FUNCTION__;
        if(function_exists("mysqli_insert_id"))
            $this->quickString = mysqli_insert_id($connection);
        else
            $this->quickString = mysql_insert_id($connection);
        return $this;
    }

    public function dbGetOne($query, $default = '', $connection_id = 0){
        if(is_string($query)){
            $connection_id = (int)$connection_id;
            if(!$this->dbQuery($query, $connection_id)){
                $this->quickString = $default;
                return $this;
            }
            if($this->dbNumRows($this->dbResult[$connection_id])->toInt() == 0){
                $this->functionName = __FUNCTION__;
                $this->quickString = $default;
                return $this;
            }
            if(function_exists("mysqli_fetch_array")){
                $arr = mysqli_fetch_array($this->dbResult[$connection_id]);
            }else{
                $arr = mysql_fetch_array($this->dbResult[$connection_id]);
            }
            $this->functionName = __FUNCTION__;
            $this->quickString = $arr[0];
        }
        return $this;
    }

    public function dbGetOneNr($query, $default = '', $connection_id = 0){
        if(is_string($query)){
            $connection_id = (int)$connection_id;
            $this->dbQueries++;
            if(function_exists("mysqli_query")){
                if(!$sql = mysqli_query($this->db[$connection_id], $query)){
                    $this->functionName = __FUNCTION__;
                    $this->quickString = $default;
                    return $this;
                }
            }else{
                if(!$sql = mysql_query($query, $this->db[$connection_id])){
                    $this->functionName = __FUNCTION__;
                    $this->quickString = $default;
                    return $this;
                }
            }
            if(function_exists("mysqli_num_rows")){
                if(mysqli_num_rows($sql) == 0){
                    $this->functionName = __FUNCTION__;
                    $this->quickString = $default;
                    return $this;
                }
            }else{
                if(mysql_num_rows($sql) == 0){
                    $this->functionName = __FUNCTION__;
                    $this->quickString = $default;
                    return $this;
                }
            }
            if(function_exists("mysqli_fetch_array"))
                $arr = mysqli_fetch_array($sql);
            else
                $arr = mysql_fetch_array($sql);
            $this->quickString = $arr[0];
            return $this;
        }
        return $this;
    }

    public function dbNumQueries(){
        $this->functionName = __FUNCTION__;
        $this->quickString = $this->dbQueries;
        return $this;
    }

    public function dbRow($query = null, $connection_id = 0){
        if(is_string($query)){
            $this->dbQuery($query, $connection_id);
            if(function_exists("mysqli_fetch_assoc"))
                $this->dbRow = mysqli_fetch_assoc($this->dbResult[$connection_id]);
            else
                $this->dbRow = mysql_fetch_assoc($this->dbResult[$connection_id]);
            $this->functionName = __FUNCTION__;
            $this->quickString = $this->dbRow;
        }elseif($query == null){
            if(is_int($connection_id)){
                if(function_exists("mysqli_fetch_assoc"))
                    $this->dbRow = mysqli_fetch_assoc($this->dbResult[$connection_id]);
                else
                    $this->dbRow = mysql_fetch_assoc($this->dbResult[$connection_id]);
            }elseif(is_resource($connection_id)){
                return $this;
            }
            $this->functionName = __FUNCTION__;
            $this->list = $this->dbRow;
            return $this->dbRow;
        }
        return $this;
    }

    public function dbField($name, $default = ''){
        $this->functionName = __FUNCTION__;
        if(isset($this->dbRow[$name])){
            $this->quickString = $this->dbRow[$name];
            return $this;
        }
        $this->quickString = $default;
        return $this;
    }

    public function dbNumRows($result = null, $connection_id = 0){
        if($result == null)
            $result = $this->dbResult[$connection_id];
        $this->functionName = __FUNCTION__;
        if(function_exists("mysqli_num_rows")){
            $this->quickString = mysqli_num_rows($result);
        }else{
            $this->quickString = mysql_num_rows($result);
        }
        return $this;
    }

    public function dbEscape($string = null, $connection_id = 0){
        if(empty($this->db)){
            $connection_id = $this->dbConnect();
            if($this->db[$connection_id] === false){
                return false;
            }
        }
        $connection_id = (int)$connection_id;
        if(isset($this->db[$connection_id]))
            $connection    = $this->db[$connection_id];
        else
            return $this;
        if($string == null){
            if(function_exists("mysqli_real_escape_string"))
                $this->quickString = mysqli_real_escape_string($connection, $this->quickString);
            else
                $this->quickString = mysql_real_escape_string($this->quickString, $connection);
        }else{
            if(function_exists("mysqli_real_escape_string"))
                $this->quickString = mysqli_real_escape_string($connection, $string);
            else
                $this->quickString = mysql_real_escape_string($string, $connection);
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function dbCols($result = null){
        if($result == null)
            $result = $this->dbResult;
        $this->functionName = __FUNCTION__;
        if(function_exists("mysqli_fetch_fields"))
            $this->list = mysqli_fetch_fields($result);
        else
            $this->list = mysql_fetch_fields($result);
        return $this;
    }

    /*
     * Threading Methods
     *
     * PHP does not fully support threading, so we had to make a workaround to implement
     * a feature that acts like threads. To get threading to work on your server, your
     * server must have a web server installed on it (this won't work with only php installed
     * on the server).
     */

    public function process($file){
        $this->threadCount();
        $new = $this->createEmptyThread();
        $this->threads[$new]['thread'] = fsockopen($this->host, $this->port);
        $this->threads[$new]['processing'] = true;
        $this->threads[$new]['thread_id'] = $new;
        //$this->threads[$new] = fopen($file, 'rb');
        if(!$this->threads[$new]['thread']){
            return false;
        }
        $this->processing = true;
        $out = "GET $file HTTP/1.1\r\n";
        $out .= "Host: $this->host\r\n";
        $out .= "Connection: Close\r\n\r\n";

        stream_set_blocking($this->threads[$new]['thread'], false);
        stream_set_timeout($this->threads[$new]['thread'], 86400);
        fwrite($this->threads[$new]['thread'], $out);
        $this->functionName = __FUNCTION__;
        return (object)$this->threads[$new];
    }

    public function isProcessing($pid){
        $this->functionName = __FUNCTION__;
        return (bool)$this->threads[$pid]['processing'];
    }

    public function threadCount(){
        $this->functionName = __FUNCTION__;
        return $this->thread_count;
    }

    public function createEmptyThread(){
        $count = $this->thread_count++;
        $this->threads[$count] = null;
        $this->functionName = __FUNCTION__;
        return $count;
    }

    public function pollThread($thread, $output = false){
        if(is_object($thread)){
            $thread_id = $thread->thread_id;
        }elseif(is_int($thread)){
            $thread_id = $thread;
        }else{
            return false;
        }
        $pointer = $this->threads[$thread_id]['thread'];
        $pid     = $thread_id;
        if(isset($this->threads[$pid])){
            $this->threads[$pid]['processing'] = true;
        }else{
            return false;
        }
        if($pointer === false){
            $this->exit[] = $pid;
            $this->threads[$pid]['processing'] = false;
            return false;
        }
        if(is_resource($pointer)){
            $opt = fread($pointer, 10000);
            if(feof($pointer)){
                fclose($pointer);
                $this->exit[] = $pid;
                $this->threads[$pid]['processing'] = false;
                return false;
            }
            if($output){
                echo $opt;
            }
        }else{
            $this->threads[$pid]['processing'] = false;
            return false;
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function poll($output = false){
        foreach($this->threads as $thread){
            $this->pollThread($thread['thread_id'], $output);
        }
        $processing = false;
        foreach($this->threads as $thread){
            if((bool)$thread['processing']){
                $processing = true;
                break;
            }
        }
        $this->processing = $processing;
        if(count($this->threads) > 0){
            $this->functionName = __FUNCTION__;
            return true;
        }
        return false;
    }

    /*
     * Sockets
     */

    public function socketConnect($port = 5565){
        $sock_id = $this->sockId;
        $socket  = socket_create_listen($port);
        if($socket != false){
            $this->sockets[$sock_id] = $socket;
            $this->sockId++;
        }else{
            return 0;
        }
        return $sock_id;
    }

    public function say($buffer, $host = "localhost", $port = 5565){
        $fp = fsockopen($host, $port);
        fwrite($fp, $buffer);
        fclose($fp);
        $this->quickString = $buffer;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function sayTo($client, $buffer){
        socket_write($client, $buffer);
        return $this;
    }

    public function hear($socket_id = 0, &$client = null, $length = 1024){
        if(!isset($this->sockets[$socket_id]))
            $socket_id = $this->socketConnect();
        $socket    = $this->sockets[$socket_id];
        if(is_resource($socket)){
            $this->allow($socket_id, $who);
            $client = $who;
            $read   = socket_read($client, $length);
            if(is_string($read)){
                if(strlen($read) > 0){
                    $this->quickString = $read;
                    $this->functionName = __FUNCTION__;
                    return $this;
                }
            }
        }
        return false;
    }

    public function allow($socket_id = 0, &$client = null){
        if(!isset($this->sockets[$socket_id]))
            $socket_id = $this->socketConnect();
        $socket    = $this->sockets[$socket_id];
        $this->clients[] = $client    = socket_accept($socket);
        $this->functionName = __FUNCTION__;
        return true;
    }

    public function clientClose($client){
        $client_id = array_search($client, $this->clients);
        unset($this->clients[$client_id]);
        socket_close($client);
        unset($client);
        return $this;
    }

    /*
     * Internal list functions
     * Commands to modify the internal list/array
     */

    public function push(){
        $this->functionName = __FUNCTION__;
        $args = func_get_args();
        foreach($args as $arg){
            $this->list[] = $arg;
        }
        return $this;
    }

    public function clean(){
        $this->functionName = __FUNCTION__;
        $this->list = array();
        return $this;
    }

    /*
     * Command Line Interface
     * Easy access to commands that are run through the command line
     */

    public function strIn(){
        $input = fgets(STDIN);
        $this->quickString = preg_replace("/\r|\n/", "", $input);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function strOut($output, $space = true){
        if((bool)$space)
            $output .= " ";
        fwrite(STDOUT, $output);
        return $this;
    }

    public function ping($host, $count = 4, $live_feedback = true){
        $param = ($this->os() == "windows") ? "-n" : "-c";
        if($live_feedback)
            $this->command("ping $host $param $count", true);
        else{
            $this->quickString = $this->command("ping $host $param $count", false);
            $this->functionName = __FUNCTION__;
            return $this;
        }
    }

    public function command($cmd, $live_feedback = false){
        if((bool)$live_feedback){
            passthru($cmd);
        }else{
            $this->quickString = shell_exec($cmd);
            $this->functionName = __FUNCTION__;
            return $this;
        }
    }

    public function in($in){
        $this->functionName = __FUNCTION__;
        $args = func_get_args();
        $i    = 0;
        foreach($args as $arg){
            if($arg == $in && $i > 0)
                return $this;
            $i++;
        }
        return false;
    }

    public function empties(){
        $this->functionName = __FUNCTION__;
        $args = func_get_args();
        $this->list = array();
        foreach($args as $k => $arg){
            if(empty($arg))
                $this->list[] = $k;
        }
        if(count($this->list) > 0)
            return true;
        return false;
    }

    public function blanks(){
        $this->functionName = __FUNCTION__;
        $args = func_get_args();
        $this->list = array();
        foreach($args as $k => $arg){
            if($this->blank($arg))
                $this->list[] = $k;
        }
        if(count($this->list) > 0)
            return true;
        return false;
    }

    public function ifelse(){
        $this->functionName = __FUNCTION__;
        $args  = func_get_args();
        $nargs = count($args);
        for($i     = 0; $i < $nargs; $i+=2){
            if($i == $nargs - 1 && is_callable($args[$i])){
                $this->quickString = call_user_func($args[$i]);
            }elseif($args[$i]){
                if(is_callable($args[$i + 1])){
                    $this->quickString = call_user_func($args[$i + 1]);
                    break;
                }
            }
        }
        return $this;
    }

    public function random(){
        $this->functionName = __FUNCTION__;
        $args = func_get_args();
        if(count($args) < 2){
            return rand(0, getrandmax());
        }
        $vals = array();
        foreach($args as $arg){
            $vals[] = $arg;
        }
        $num    = mt_rand(0, count($vals) - 1);
        $this->quickString = $vals[$num];
        return $this;
    }

    /*
     * Session Manager
     *
     * The session manager manages all sessions for the phpLive library and plugins for the library.
     * When using the phpLive library it is recommended that you use the session manager to manage
     * all of your sessions, instead of manually adding your own. It is not required to use your own
     * sessions but will make it easier on you the programmer. This manager should prevent plugins
     * from sharing session names.
     *
     */

    public function startSession(){
        if(!isset($_SESSION)){
            if(!headers_sent()){
                session_start();
                $this->functionName = __FUNCTION__;
                return $this;
            }
        }
        return false;
    }

    /*
     * setSession will set a key and value for your session, this splits one plugin sessions from another
     * plugins sessions, so two different plugins can have the same session key/value without interferance.
     */

    public function setSession($sessionKey, $sessionValue = null){
        $this->startSession();
        $ini = $this->allPluginSettings();
        if(!is_array($ini)){
            $ini = array();
        }
        foreach($ini as $section){
            if(isset($section['className'])){
                if($section['className'] == $this->getCalledClass()){
                    if($sessionValue == null)
                        $sessionValue                                             = $this->quickString;
                    $_SESSION['phpLive'][$section['sessionRef']][$sessionKey] = $sessionValue;
                    $this->functionName = __FUNCTION__;
                    return $this;
                }
            }
        }
        $_SESSION['phpLive'][$sessionKey]                         = $sessionValue;
        return $this;
    }

    /*
     * getSession will get a session from your plugins session, so if two plugins use the same
     * session key/value as your plugin, the other plugins session will be ignored, and the one
     * from the current working plugin will be returned.
     */

    public function getSession($sessionKey, $default = ""){
        $this->startSession();
        $ini = $this->allPluginSettings();
        if(!is_array($ini)){
            $ini = array();
        }
        foreach($ini as $section){
            if(isset($section['className'])){
                if($section['className'] == $this->getCalledClass()){
                    if(isset($_SESSION['phpLive'][$section['sessionRef']][$sessionKey])){
                        $this->functionName = __FUNCTION__;
                        $this->quickString = $_SESSION['phpLive'][$section['sessionRef']][$sessionKey];
                        return $this;
                    }
                }
            }
        }
        $this->functionName = __FUNCTION__;
        if(isset($_SESSION['phpLive'][$sessionKey])){
            $this->quickString = $_SESSION['phpLive'][$sessionKey];
        }else{
            $this->quickString = $default;
        }
        return $this;
    }

    /*
     * getSessions will return an array of sessions for your plugin
     */

    public function getSessions(){
        $this->startSession();
        $this->allPluginSettings();
        $ret = array();
        $this->getCalledPlugin();
        /* foreach($_SESSION['phpLive'][$ini['sessionRef']] as $key => $val){
          $ret[$key] = $val;
          }
          $this->functionName = __FUNCTION__; */
        return $ret;
    }

    /*
     * removeSession will remove a session for the current working plugin.
     * When this is called within a plugin, it will delete a particular session value
     * $live->removeSession("my_session");
     * The above will remove the session with the key "my_session" and leave other alone.
     */

    public function removeSession($sessionKey){
        if(is_string($sessionKey)){
            $sessionKey = array($sessionKey);
        }
        $this->startSession();
        $ini = $this->allPluginSettings();
        foreach($ini as $section){
            if($section['className'] == $this->getCalledClass()){
                foreach($sessionKey as $key){
                    if(isset($_SESSION['phpLive'][$section['sessionRef']][$key])){
                        unset($_SESSION['phpLive'][$section['sessionRef']][$key]);
                    }
                }
                $this->functionName = __FUNCTION__;
                return $this;
            }
        }
        return false;
    }

    public function removeSessions(){
        $this->startSession();
        $ini = $this->allPluginSettings();
        foreach($ini as $section){
            if($section['className'] == $this->getCalledClass()){
                unset($_SESSION['phpLive'][$section['sessionRef']]);
                $this->functionName = __FUNCTION__;
                return $this;
            }
        }
        return false;
    }

    /*
     * killSession will kill the current phplive session including ALL plugins, this will
     * ignore sessions created outside the phplive session.
     */

    public function killSession(){
        $this->startSession();
        unset($_SESSION['phpLive']);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * killSessions will kill ALL sessions, including those created by plugins
     */

    public function killSessions(){
        $this->startSession();
        unset($_SESSION);
        session_destroy();
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * convert_smart will convert smart quotes, apostrophes, mdash, ndash and others to normal state
     * pass it a string, and it will return an unformatted non-smart string
     */

    public function convertSmart($string = null){
        if($string == null)
            $string = $this->quickString;
        $search = array(chr(145), "‘", chr(146), "’", chr(147), "“", chr(148), "�?", chr(151), "—", chr(150), "–", chr(133), "…", chr(149), "•");
        $replace = array("'", "'", "'", "'", '"', '"', '"', '"', '--', '--', '-', '-', '...', '...', "&bull;", "&bull;");
        $this->quickString = str_replace($search, $replace, $string);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * require_login is very similar to is_logged_in, the difference is this will redirect the user
     * automatically where as is_logged_in returns ture or false
     * Example:
     * $this->require_login();
     *
     * if the user is not logged in they will be redirected, otherwise you can chain this method
     */

    public function requireLogin($redirect = "/", $session_name = 'logged'){
        $this->startSession();
        if(!$this->isLoggedIn($session_name)){
            header("Location: $redirect");
            exit;
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * This will set an easy to use way to check if someone is logged in or not, by default the session is called
     * "logged" All you need to do is run this when a successfull login happens, you can then use
     * is_logged_in to check if the user is logged in.
     * Example:
     * $live->set_logged_in();
     */

    public function setLoggedIn($session_name = 'logged'){
        $this->startSession();
        $_SESSION[$session_name] = true;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function setLoggedOut($session_name = 'logged'){
        $this->startSession();
        $_SESSION[$session_name] = false;
        unset($_SESSION[$session_name]);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * is_logged_in check to see if someone is logged in
     * Example:
     * if(!$live->is_logged_in())
     *        $live->location("./login.php");
     */

    public function isLoggedIn($session_name = 'logged'){
        $this->startSession();
        if(isset($_SESSION[$session_name])){
            if($_SESSION[$session_name]){
                return $this;
                $this->functionName = __FUNCTION__;
            }
            return false;
        }
        return false;
    }

    public function setError($err_id, $err_descr){
        $this->errors[$err_id] = $err_descr;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function getError($err_id = null){
        $this->functionName = __FUNCTION__;
        if($err_id == null)
            return $this->errors;
        if(isset($this->errors[$err_id]))
            return $this->errors[$err_id];
        return false;
    }

    /*
     * query_remove removes a value from your query string, if your query string looks like this:
     * animal=cat&age=2&color=red
     * $live->query_remove("color")->query_remove("age")->query_add("weight", 10);
     * your new query would now look like ths:
     * animal=cat&weight=10
     */

    public function qRemove($key){
        if(is_object($key) || is_array($key)){
            $key = (object)$key;
            foreach($key as $k => $v){
                unset($this->urlQuery[$k]);
            }
        }else{
            unset($this->urlQuery[$key]);
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function qModify($object){
        if(is_object($object)){
            foreach($object as $key => $value){
                if(empty($value)){
                    $this->qRemove($key);
                }else{
                    $this->qAdd($key, $value);
                }
            }
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * query_add adds values to a query string (see query_remove)
     */

    public function qAdd($key, $val = ""){
        if(is_object($key) || is_array($key)){
            $key = (object)$key;
            foreach($key as $k => $v){
                $this->urlQuery[$k] = $v;
            }
        }else{
            $this->urlQuery[$key] = $val;
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * query_string returns the current query string
     */

    public function qString(){
        $this->functionName = __FUNCTION__;
        if(isset($_SERVER['QUERY_STRING']))
            return $_SERVER['QUERY_STRING'];
        else
            return false;
    }

    /*
     * get gets any value passed though your address bar ($_GET)
     * (see post)
     */

    public function get($key = "", $default = ""){
        if(empty($key)){
            if(isset($_GET) && !empty($_GET)){
                return true;
            }
            return false;
        }
        if(isset($_GET[$key])){
            $this->quickString = $_GET[$key];
        }else{
            $this->quickString = $default;
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function gets(){
        $this->list = $_GETS;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * post gets a post value that was sent via post, if the post value is not found a default value is set
     * $live->post("first_name"); // if first name is not found return empty string
     * a default value is useful, now you don't need to know if a value is set or not, if it is not set
     * your default value is returned.
     */

    public function post($key = "", $default = ""){
        if(empty($key)){
            if(isset($_POST) && !empty($_POST)){
                return true;
            }
            return false;
        }
        if(isset($_POST[$key])){
            $this->quickString = $_POST[$key];
            //return $_POST[$key];
        }else{
            $this->quickString = $default;
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function posts(){
        $this->list = $_POST;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function gp($key, $default = ""){
        if(isset($_GET[$key]))
            $this->quickString = $_GET[$key];
        elseif(isset($_POST[$key]))
            $this->quickString = $_POST[$key];
        else
            $this->quickString = $default;
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * file returns an array of files
     * $upload = $live->file("my_upload")
     * echo $upload['name'];
     */

    public function file($key, $default = ""){
        $this->functionName = __FUNCTION__;
        if(isset($_FILES[$key])){
            $this->list = $_FILES[$key];
            return $this;
        }
        $this->quickString = $default;
        return $this;
    }

    public function fromList($key, $default = ""){
        $this->functionName = __FUNCTION__;
        if(isset($this->list[$key])){
            $this->quickString = $this->list[$key];
            return $this;
        }
        $this->quickString = $default;
        return $this;
    }

    /*
     * save_upload works exactly the same as move_uploaded file, the only difference is
     * you can chain this to other methods
     */

    public function saveUpload($tmp_file, $new_file){
        if(move_uploaded_file($tmp_file, $new_file)){
            $this->functionName = __FUNCTION__;
            return $this;
        }
        return false;
    }

    /*
     * this will send you to a new location, this is the same as using a header redirect
     * this will also exit automatically for you, so you don't have to call exit after it.
     * Example:
     * $this->location("./");
     */

    public function location($path){
        $this->functionName = __FUNCTION__;
        if(!headers_sent()){
            header("location: $path");
            exit;
        }
    }

    public function content($type){
        $this->functionName = __FUNCTION__;
        if(!headers_sent())
            header("Content-Type: $type");
    }

    /*
     * extract extracts data from a string, such as:
     * - Numbers
     * - Letters
     * - Uppercase Letters
     * - Lowercase Letters
     * - Symbols
     */

    public function extract($value, $type = EXTRACT_NUMBER){
        switch($type){
            case EXTRACT_NUMBER:
                $this->quickString = preg_replace("/[^0-9]/", "", $value);
                break;
            case EXTRACT_LETTER:
                $this->quickString = preg_replace("/[^a-zA-Z]/", "", $value);
                break;
            case EXTRACT_NUMBER_LETTER:
                $this->quickString = preg_replace("/[^a-zA-Z0-9]/", "", $value);
                break;
            case EXTRACT_UPPER:
                $this->quickString = preg_replace("/[^A-Z]/", "", $value);
                break;
            case EXTRACT_LOWER:
                $this->quickString = preg_replace("/[^a-z]/", "", $value);
                break;
            case EXTRACT_SYMBOL:
                $this->quickString = preg_replace("/[a-zA-Z0-9 ]/", "", $value);
                break;
            case EXTRACT_PHONE:
                $value = $this->remove($value, REMOVE_SYMBOL);
                $value = $this->remove($value, REMOVE_WHITE_SPACE);
                preg_match_all("/\d{10}/", $value, $matches);
                $this->list = $matches[0];
                break;
            case EXTRACT_EMAIL:

                break;
            case EXTRACT_LINKS:
                $this->getLinks($value);
                break;
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function remove($value = null, $type = REMOVE_WHITE_SPACE){
        if($value == null)
            $value = $this->quickString;
        switch($type){
            case REMOVE_SYMBOL:
                $this->quickString = preg_replace("/[^a-zA-Z0-9 ]/", "", $value);
                break;
            case REMOVE_WHITE_SPACE:
                $this->quickString = preg_replace("/ /", "", $value);
                break;
            case REMOVE_LETTER:
                $this->quickString = preg_replace("/[a-zA-Z]/", "", $value);
                break;
            case REMOVE_NUMBER:
                $this->quickString = preg_replace("/[0-9]/", "", $value);
                break;
            case REMOVE_LOWER:
                $this->quickString = preg_replace("/[a-z]/", "", $value);
                break;
            case REMOVE_UPPER:
                $this->quickString = preg_replace("/[A-Z]/", "", $value);
                break;
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * Checks to see if a value is in a valid format. returns true/false
     * Validate an email address:
     * var_dump($live->is_valid("my-email@mysite.com")); //prints bool(true)
     * Validate a date:
     * var_dump($live->is_valid("2011-03-38", VALID_DATE)); //prints bool(false)
     * Validate a time:
     * var_dump($live->is_valid("24:12:08", VALID_TIME)); //prints bool(false)
     * Validate a DateTime:
     * var_dump($live->is_valid("2000-123-21 24:12:08", VALID_DATETIME)); //prints bool(false)
     * Validate a URL
     * var_dump($live->is_valid("http://phplive.org/docs", VALID_URL)); //prints bool(true)
     */

    public function isValid($value, $type = VALID_EMAIL){
        switch($type){
            case VALID_EMAIL:
                if(filter_var($value, FILTER_VALIDATE_EMAIL)){
                    $this->functionName = __FUNCTION__;
                    return true;
                }
                break;
            case VALID_DATE:
                if(is_int($value)){
                    $time = strtotime($value);
                    list($year, $month, $day) = explode("-", date("Y-m-d", $time));
                }else{
                    list($year, $month, $day) = explode("-", $value);
                }
                $this->functionName = __FUNCTION__;
                return checkdate($month, $day, $year);
                break;
            case VALID_TIME:
                list($hour, $minute, $second) = explode(":", $value);
                if($hour >= 0 && $hour <= 23){
                    if($minute >= 0 && $minute <= 59){
                        if($second >= 0 && $minute <= 59){
                            $this->functionName = __FUNCTION__;
                            return true;
                        }
                    }
                }
                break;
            case VALID_DATETIME:
                if(is_int($value)){
                    $value = $this->date($value);
                }
                list($dates, $times) = explode(" ", $value);
                list($hour, $minute, $second) = explode(":", $times);
                list($year, $month, $day) = explode("-", $dates);
                if(checkdate($month, $day, $year)){
                    if($hour >= 0 && $hour <= 23){
                        if($minute >= 0 && $minute <= 59){
                            if($second >= 0 && $minute <= 59){
                                $this->functionName = __FUNCTION__;
                                return true;
                            }
                        }
                    }
                }
                break;
            case VALID_URL:
                if(filter_var($value, FILTER_VALIDATE_URL)){
                    $this->functionName = __FUNCTION__;
                    return true;
                }
                break;
        }
        return false;
    }

    public function blank($string = null){
        if($string == null)
            $string = $this->quickString;
        $string = str_replace(array(" ", "\t", "\n", "\r"), "", $string);
        $this->functionName = __FUNCTION__;
        return (bool)empty($string);
    }

    /*
     * Date / Time Methods
     *
     * Most methods are very similar to the php equivalant.
     */

    public function toTimestamp($date = null){
        if(is_null($date))
            $date = $this->now();
        $this->functionName = __FUNCTION__;
        $this->quickString = date("Y-m-d H:i:s", strtotime($date));
        return $this;
    }

    /*
     * Extracts the date from a timestamp
     * Example:
     * echo $live->date("2011-07-10 12:12:10"); // prints 2011-07-10
     */

    public function date($date = null){
        if(is_null($date))
            $date = $this->now();
        if(!is_int($date)){
            $date = strtotime($date);
        }
        $this->functionName = __FUNCTION__;
        return date("Y-m-d", $date);
    }

    /*
     * Extracts the year from a date.
     * Example:
     * echo $live->year("2011-07-10 12:12:10"); // prints 2011
     */

    public function year($date = null){
        if(is_null($date))
            $date = $this->now();
        if(!is_int($date)){
            $date = strtotime($date);
        }
        $this->functionName = __FUNCTION__;
        return date("Y", $date);
    }

    /*
     * Extracts the month from a date.
     * Example:
     * echo $live->month("2011-07-10 12:12:10"); // prints 07
     */

    public function month($date = null){
        if(is_null($date))
            $date = $this->now();
        if(!is_int($date)){
            $date = strtotime($date);
        }
        $this->functionName = __FUNCTION__;
        return date("m", $date);
    }

    /*
     * Extracts the day from a date.
     * Example:
     * echo $live->day("2011-07-10 12:12:10"); // prints 10
     */

    public function day($date = null){
        if(is_null($date))
            $date = $this->now();
        if(!is_int($date)){
            $date = strtotime($date);
        }
        $this->functionName = __FUNCTION__;
        return date("d", $date);
    }

    /*
     * Extracts the hour from a date.
     * Example:
     * echo $live->hour("2011-07-10 12:12:10"); // prints 12
     */

    public function hour($date = null){
        if(is_null($date))
            $date = $this->now();
        if(!is_int($date)){
            $date = strtotime($date);
        }
        $this->functionName = __FUNCTION__;
        return date("H", $date);
    }

    /*
     * Extracts the minute from a date.
     * Example:
     * echo $live->minute("2011-07-10 12:12:10"); // prints 12
     */

    public function minute($date = null){
        if(is_null($date))
            $date = $this->now();
        if(!is_int($date)){
            $date = strtotime($date);
        }
        $this->functionName = __FUNCTION__;
        return date("i", $date);
    }

    /*
     * Extracts the second from a date.
     * Example:
     * echo $live->second("2011-07-10 12:12:10"); // prints 10
     */

    public function second($date = null){
        if(is_null($date))
            $date = $this->now();
        if(!is_int($date)){
            $date = strtotime($date);
        }
        $this->functionName = __FUNCTION__;
        return date("s", $date);
    }

    /*
     * Returns the current date/time (YYYY-MM-DD HH:MM:SS)
     */

    public function now(){
        $this->functionName = __FUNCTION__;
        return date("Y-m-d H:i:s");
    }

    /*
     * Returns the current date (YYYY-MM-DD)
     */

    public function curdate(){
        $this->functionName = __FUNCTION__;
        return date("Y-m-d");
    }

    /*
     * Returns the current time (HH:MM:SS)
     */

    public function curtime(){
        $this->functionName = __FUNCTION__;
        return date("H:i:s");
    }

    /*
     * Takes a unix timestamp and converts it to a readable date (YYYY-MM-DD HH:MM:SS)
     */

    public function fromUnixtime($time = null){
        $time = (int)$time;
        if($time == 0){
            $time = $this->unixTimestamp();
        }
        $this->functionName = __FUNCTION__;
        return date("Y-m-d H:i:s", $time);
    }

    /*
     * Converts a valid date to a unix timestamp
     */

    public function unixTimestamp($date = null){
        if($date == null){
            return strtotime(date("Y-m-d H:i:s"));
        }
        $this->functionName = __FUNCTION__;
        return strtotime($date);
    }

    /*
     * date_sub subtracts from $start_date returning a final date
     * Example
     * echo $live->date_sub("2011-07-05", "1 week"); // prints 2011-06-28 00:00:00
     */

    public function dateSub($start_date, $interval){
        if(!is_int($start_date))
            $start = date("Y-m-d H:i:s", strtotime($start_date));
        else
            $start = date("Y-m-d H:i:s", $start_date);
        $this->functionName = __FUNCTION__;
        return date("Y-m-d H:i:s", strtotime($start." -".$interval));
    }

    /*
     * date_add adds to $start_date returning a final date
     * Example
     * echo $live->date_add("2011-07-05", "1 week"); // prints 2011-07-12 00:00:00
     */

    public function dateAdd($start_date, $interval){
        if(!is_int($start_date))
            $start = date("Y-m-d H:i:s", strtotime($start_date));
        else
            $start = date("Y-m-d H:i:s", $start_date);
        $this->functionName = __FUNCTION__;
        return date("Y-m-d H:i:s", strtotime($start." +".$interval));
    }

    /*
     * datediff takes a start and end date (preferably YYYY-MM-DD HH:MM:SS but it can convert many other formats)
     * the optional 3 parameter allows for you to return a more exact day, with decimal, by default this is off
     * Example:
     * echo $live->datediff("2011-07-10", "2011-07-09"); // prints 1
     * echo $live->datediff("2011-07-10", "2011-06-09"); // prints 31
     * echo $live->datediff("2010-07-10", "2011-07-09"); // prints -364
     */

    public function datediff($start_date, $end_date, $exact = false){
        if(!is_int($start_date)){
            if((bool)$exact)
                $start = strtotime($start_date);
            else
                $start = strtotime(date("Y-m-d", strtotime($start_date)));
        }
        if(!is_int($end_date)){
            if((bool)$exact)
                $end = strtotime($end_date);
            else
                $end = strtotime(date("Y-m-d", strtotime($end_date)));
        }
        $this->functionName = __FUNCTION__;
        return ($start - $end) / 60 / 60 / 24;
    }

    public function dateFormat($date, $format){
        if(!is_int($date)){
            $date = strtotime($date);
        }
        $this->functionName = __FUNCTION__;
        $this->quickString = date($format, $date);
        return $this;
    }

    public function age($dob, $start = "now"){
        if(is_int($dob))
            $dob = $this->date($dob);
        if(!$this->isValid($dob, VALID_DATE))
            return false;
        if($start == "now")
            $now = $this->curdate();
        else{
            if(!$this->isValid($start, VALID_DATE))
                return false;
            $now       = $start;
        }
        $now_year  = $this->year($now);
        $now_month = $this->month($now);
        $now_day   = $this->day($now);
        $dob_year  = $this->year($dob);
        $dob_month = $this->month($dob);
        $dob_day   = $this->day($dob);
        $age       = $now_year - $dob_year;
        if((int)($now_month.$now_day) < (int)($dob_month.$dob_day))
            $age -= 1;
        $this->functionName = __FUNCTION__;
        return $age;
    }

    /*
     * Miscellanous Methods
     */
    /*
     * download allows for simple downloading of files or strings.
     * To download a file, pass the location of the file to download as parameter 1 (example: ../../myFile.jpg)
     * To download a string, pass the string as parameter 1 (example: This text will show up in the download)
     * To give the file a download name, pass the name as parameter 2 (example: download.jpg)
     * By default downloads are set to file using: DOWNLOAD_FILE to download a string use DOWNLOAD_STRING
     */

    public function download($download_data, $download_name, $type = DOWNLOAD_FILE){
        header("Pragma: public;");
        header("Expires: 0;");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0;");
        if(!preg_match("/^\/tmp\/zip/", $this->tmpFile)){
            header("Content-Type: application/force-download;");
            header("Content-Type: application/octet-stream;");
            header("Content-Type: application/download;");
        }
        header("Content-Transfer-Encoding: binary;");
        header("Content-Disposition: attachment; filename=$download_name;");
        if($type == DOWNLOAD_FILE){
            if($download_data == PHP_TMPFILE){
                $download_data = $this->tmpFile;
            }
            header("Content-Length: ".filesize($download_data).";");
            readfile($download_data);
        }elseif($type == DOWNLOAD_STRING){
            header("Content-Length: ".mb_strlen($download_data).";");
            echo $download_data;
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function zip($files = array(), $flag = ZIP_NEW){
        $zip_id = $this->zipId;
        $this->zip[] = new ZipArchive();
        $this->zipId++;
        $this->tmpFile = tempnam("/tmp/", "zip");
        if($flag == ZIP_NEW){
            $this->zip[$zip_id]->open($this->tmpFile, ZipArchive::CREATE);
            $files = (array)$files;
            if(!empty($files)){
                foreach($files as $loc => $file){
                    if(is_int($loc)){
                        if(is_file($file)){
                            $this->zip[$zip_id]->addFile($file);
                        }else{
                            $this->zip[$zip_id]->addEmptyDir($file);
                        }
                    }else{
                        if(is_file($loc)){
                            $this->zip[$zip_id]->addFile($loc, $file);
                        }else{
                            $this->zip[$zip_id]->addEmptyDir($file);
                        }
                    }
                }
            }
            $this->zip[$zip_id]->close();
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function tmpDelete($filename = null){
        if($filename == null)
            $filename = $this->tmpFile;
        else{
            if(!preg_match("/^\/tmp\//", $filename)){
                $filename = "/tmp/".preg_replace("/^\//", "", $filename);
            }
        }
        unlink($filename);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * inet_aton will convert a 16 bit ip address to an 8 bit int
     * For example 104.235.18.2 becomes 1760236034
     */

    public function inetAton($ip_address){
        $this->quickString = (int)sprintf("%u\n", ip2long($ip_address));
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * inet_ntoa will reverse inet_aton taking an int and returning an ip address
     * For example 1760236034 becomes 104.235.18.2
     */

    public function inetNtoa($number){
        $this->quickString = long2ip((int)$number);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function callback($callback, &$return = null){
        $return = call_user_func($callback);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function mail($to, $from, $subject, $message, $type = MAIL_HTML, $filepath = ""){
        foreach($to as $name => $email){
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "To: $name <$email>\r\n";
            $headers .= "From: {$from[0]} <{$from[1]}>\r\n";
            if($type == MAIL_ATTACHMENT){
                $random_hash   = md5(date('r', time()));
                $mime_boundary = "==Multipart_Boundary_x{$random_hash}x";
                $headers .= "Content-Type: multipart/mixed; boundary=\"$mime_boundary\";\r\n";
                $message       = "--$mime_boundary\r\n".
                        "Content-Type: multipart/mixed; boundary=\"{$mime_boundary}\";\r\n".
                        "Content-Transfer-Encoding: 7bit\r\n\r\n".
                        $message."\r\n\r\n";
                if(is_string($filepath))
                    $filepath      = array($filepath);
                $filepath = array_filter($filepath);
                foreach($filepath as $file){
                    if(empty($file)){
                        $data = chunk_split(base64_encode($this->content));
                        $file = $this->filebasename;
                    }else{
                        $data = chunk_split(base64_encode(file_get_contents($file)));
                    }
                    $message .= "--{$mime_boundary}\r\n".
                            "Content-Type: multipart/alternative; name=\"".basename($file)."\"\r\n".
                            "Content-Transfer-Encoding: base64\r\n".
                            "Content-Disposition: attachment; filename=\"".basename($file)."\";\r\n\r\n".
                            $data."\r\n\r\n".
                            "--{$mime_boundary}\r\n";
                }
            }elseif($type == MAIL_HTML){
                $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            }
            mail($email, $subject, $message, $headers);
        }
        $this->functionName = __FUNCTION__;
        return $this;
    }

    public function each($callback, &$return = null, $overwrite_list = true){
        $tempArr = array();
        $last_func = $this->functionName;
        $db_funcs  = array("dbQuery");
        if(in_array($last_func, $db_funcs)){
            while($this->dbRow()){
                $tempArr[] = call_user_func($callback, $this->dbRow);
            }
            $this->quickString = implode("", $tempArr);
            $this->functionName = __FUNCTION__;
            return $this;
        }
        foreach($this->list as $key => $value){
            $val = call_user_func($callback, $key, $value);
            if(!empty($val)){
                $tempArr[] = $val;
            }
        }
        if((bool)$overwrite_list)
            $this->list = $tempArr;
        $this->functionName = __FUNCTION__;
        $return    = $tempArr;
        return $this;
    }

    public function loop($callback, $times = 0){
        $i = 0;
        while(true){
            if((int)$times > 0 && $i == $times){
                break;
            }
            $break = (bool)call_user_func($callback);
            if((bool)$break){
                break;
            }
            $i++;
        }
        return $this;
    }

    public function isMobile(){
        $this->functionName = __FUNCTION__;
        return (bool)preg_match("/".implode("|", $this->phones)."/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    public function os(){
        $this->functionName = __FUNCTION__;
        $os = php_uname('s');
        if(preg_match("/windows/i", $os))
            $this->quickString = 'windows';
        if(preg_match("/linux/i", $os))
            $this->quickString = 'linux';
        if(preg_match("/unix/i", $os))
            $this->quickString = 'unix';
        return $this;
    }

    public function format($string = null, $places = 0){
        if($string == null)
            $string = $this->quickString;
        $this->quickString = number_format($string, $places);
        return $this;
    }

    public function versionComp($version1, $version2){
        $this->quickString = version_compare($version1, $version2);
        $this->functionName = __FUNCTION__;
        return $this;
    }

    /*
     * Regex Functions
     */
    /*
     * regCount takes a regular expression and finds how many results there are
     */

    public function regCount($string, $subject = null, $delim = "/"){
        if($subject == null)
            $subject = $this->quickString;
        preg_match_all($delim.$string.$delim, $subject, $matches);
        $this->quickString = count($matches[0]);
        return $this;
    }

    /*
     * Error pages
     */

    public function e404($sendHeader = true, $echo = true, $exit = true){
        $this->functionName = __FUNCTION__;
        if(!headers_sent() && $sendHeader){
            header("HTTP/1.0 404 Not Found");
            header("Status: 404 Not Found");
        }
        $request_page = $_SERVER['REQUEST_URI'];
        $err          = <<<ERROR
<h1>404 - Page Not Found</h1>
<p>The page you are requesting (<b>$request_page</b>) was not found on this server.</p>
<p>You may have:</p>
<ul>
    <li>Followed an invalid link</li>
    <li>Accessed a page that has been removed</li>
    <li>Accessed a page that has never existed</li>
</ul>
<p>Any other possibility may have occured</p>
ERROR;
        $this->functionName = __FUNCTION__;
        if($echo){
            echo $err;
            if($exit){
                exit;
            }
        }else
            return $err;
    }

}

// End phpLive Class
// Begin phpLive auto creation of instance(s)
// Create an instace of the phpLive class
// phpLive can be called using one of the following:
// $phplive, $_live, $live, $pl, $p
$phplive = $_live   = $live    = $pl      = $p       = new phpLive();

// End phpLive auto creation of instance
// Load phpLive extensions
// phpLive extensions require php 5.3+
if(is_dir(dirname(__FILE__)."/extensions")){
    if(version_compare(PHP_VERSION, '5.3.0') >= 0){
        foreach(glob(dirname(__FILE__)."/extensions/*.php") as $phpLive_ext_file){
            require_once $phpLive_ext_file;
        }
    }
}

// Enable / Disable plugins
if((isset($disablePlugins) && (bool)$disablePlugins === false) || !isset($disablePlugins)){
    // if $plugins is not set load all plugins
    // if $plugins is set, load Named plugins (comma sperated list or an array)
    if(!isset($plugins)){
        $plugins = null;
    }
    $phplive->loadPlugins($plugins);
}

/*
 * The closing ?> is gone for a reason, adding it back may cause errors to your code.
 * An example of this is if you add the closing ?> to this file and by accedent added a space or a new line
 * after it, when you include this file into another document headers may break and give an error saying:
 * "headers have already been sent"
 * this is also true for starting sessions, or anything else that may need to modify header information.
 *
 * IF YOU ADD IT YOU HAVE BEEN WARNED!
 */