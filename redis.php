<?php

//require_once "../lib/php/vendor/autoload.php";

//비동기 이벤트 안에서 redis 객체생성해야 에러 안남.
function go(function() {
    $redis = new Swoole\Coroutine\Redis();
    //$redis = new swoole_redis();

    $redis->connect('172.17.0.1', 1234);
    $val = $redis->get('key');
    echo $val;
    echo 222;


});

go();

/*
$client = new swoole_redis;
$client->connect('172.17.0.1', 1234, function (swoole_redis $client, $result) {
    if ($result === false) {
        echo "connect to redis server failed.\n";
        return true;
    }
    $client->set('key', 'swoole', function (swoole_redis $client, $result) {
        var_dump($result);
    });
});


echo 222;
*/
?>