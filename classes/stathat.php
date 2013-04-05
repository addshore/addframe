<?php
function do_post_request($url, $data, $optional_headers = null)
{
        $params = array('http' => array(
                'method' => 'POST',
                'content' => $data
        ));
        if ($optional_headers !== null) {
                $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
                throw new Exception("Problem with $url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
                throw new Exception("Problem reading data from $url, $php_errormsg");
        }
        return $response;
}

function do_async_post_request($url, $params)
{
        foreach ($params as $key => &$val) {
                if (is_array($val)) $val = implode(',', $val);
                $post_params[] = $key.'='.urlencode($val);
        }
        $post_string = implode('&', $post_params);

        $parts=parse_url($url);

        $fp = fsockopen($parts['host'],
                isset($parts['port'])?$parts['port']:80,
                $errno, $errstr, 30);

        $out = "POST ".$parts['path']." HTTP/1.1\r\n";
        $out.= "Host: ".$parts['host']."\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: ".strlen($post_string)."\r\n";
        $out.= "Connection: Close\r\n\r\n";
        if (isset($post_string)) $out.= $post_string;

        fwrite($fp, $out);
        fclose($fp);
}

function stathat_count($stat_key, $user_key, $count)
{
        return do_async_post_request("http://api.stathat.com/c", array('key' => $stat_key, 'ukey' => $user_key, 'count' => $count));
}

function stathat_value($stat_key, $user_key, $value)
{
        do_async_post_request("http://api.stathat.com/v", array('key' => $stat_key, 'ukey' => $user_key, 'value' => $value));
}

function stathat_ez_count($email, $stat_name, $count)
{
        do_async_post_request("http://api.stathat.com/ez", array('email' => $email, 'stat' => $stat_name, 'count' => $count));
		//echo "StatHat - ".$stat_name." - Added count - '$count'\n";
}

function stathat_ez_value($email, $stat_name, $value)
{
        do_async_post_request("http://api.stathat.com/ez", array('email' => $email, 'stat' => $stat_name, 'value' => $value));
		//echo "StatHat - ".$stat_name." - Added value - '$value'\n";
}

function stathat_count_sync($stat_key, $user_key, $count)
{
         return do_post_request("http://api.stathat.com/c", "key=$stat_key&ukey=$user_key&count=$count");
}

function stathat_value_sync($stat_key, $user_key, $value)
{
        return do_post_request("http://api.stathat.com/v", "key=$stat_key&ukey=$user_key&value=$value");
}

function stathat_ez_count_sync($email, $stat_name, $count)
{
        return do_post_request("http://api.stathat.com/ez", "email=$email&stat=$stat_name&count=$count");
}

function stathat_ez_value_sync($email, $stat_name, $value)
{
        return do_post_request("http://api.stathat.com/ez", "email=$email&stat=$stat_name&value=$value");
}

?>
