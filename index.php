<?php

//$redis = new swoole_redis;


$server = new swoole_websocket_server("0.0.0.0", 80);
$server->set(array(
    'worker_num' => 4
));

//$redisClientPool = array("global"=>"ok");

//use Swoole\Coroutine\Redis as swoole_redis;



$server->on("workerStart", function ($server, $workerId){
    global $argv;
    //if($workerId != 0)return;
    echo "on() workerStart ======================================\n";
    echo "workerId=" . $workerId . 
    ", worker_num=" . $server->setting['worker_num'] 
    . ", worker_id=" . $server->worker_id
    . ", worker_pid=" . $server->worker_pid
    . "\n";

    if($workerId >= $server->setting['worker_num']) {
        echo "task worker\n";
        swoole_set_process_name("php {$argv[0]} task worker");
    } else {
        echo "event worker\n";
        swoole_set_process_name("php {$argv[0]} event worker");
    }
    
    
    //echo json_encode($server,JSON_PRETTY_PRINT);

    if($workerId ==0){

        $client = new \swoole_redis;
        $client->on("message", function (\swoole_redis $client, $data) use ($server) {
            echo "redis.on(message)--------------------------\n";
            echo json_encode($data,JSON_PRETTY_PRINT);
            // process data, broadcast to websocket clients
             if ($data[0] == 'message') {
                 echo "message recived.\n";
                 foreach($server->connections as $fd) {
                    echo "push " . $fd . "\n";
                    $server->push($fd, json_encode(["msg"=>$data[2]]));
                 }
             }else{

             }
        });
        $client->connect("172.17.0.1", 1234, function (swoole_redis $client, $data) {
            echo "redis.on(message)--------------------------";
            $client->subscribe("chat.all");
        });
    }
 });
 

 
$server->on('open', function($server, $req){
    global $redisClientPool;
    //var_dump($redisClientPool);

    echo "connection open: {$req->fd}\n";

    echo " \t쿠키.세션ID : " . $req->cookie["PHPSESSID"] . "\n";
    echo " \tremote_addr : " . $req->server["remote_addr"] . "\n";
    echo " \t입장 id: " . $req->get["userid"] . "\n";
    echo " \t입장 별명: " . $req->get["nickname"] . "\n";
    //var_dump($req);
    
    $msg = $req->get["nickname"] . "이 입장했습니다.";

    //나에게 보내기
    $server->push($req->fd, json_encode(["msg"=>$msg]));
    //echo $req->fd . "pubsub 루프가 해제 되었습니다.";    

    //알리기
    $redis = new Swoole\Coroutine\Redis();
    $redis->connect("172.17.0.1", 1234);
    $tmp = $redis->publish("chat.all",$msg);
    //var_dump($tmp);
    //echo json_encode($tmp,JSON_PRETTY_PRINT);
    $redis->close;

    /*
    $pubRedis = new swoole_redis;
    $pubRedis->connect("172.17.0.1", 1234, function (swoole_redis $pubRedis, $data) {
        echo "publish chat.all--------------------------";
        if($pubRedis->subscribe("chat.all")){
            $pubRedis->publish("chat.all",$msg);
        }else{
            echo "chat.all에 subscribe 실패 \n";
        }
        $pubRedis->close;
    });
    */
    

});

$server->on('message', function($server, $frame) {
    echo "received message: {$frame->data}\n";
    $server->push($frame->fd, json_encode(["msg"=>$frame->data]));

    //var_dump($frame);
});

$server->on('close', function($server, $fd){
    global $redisClientPool;
    echo "connection close: {$fd}\n";

    //redis client close();
    //var_dump($redisClientPool[$fd]);
    //$redisClientPool[$fd]->close();
    //$redisClientPool[$fd] = null;
});

$server->on("start", function ($server) {
    echo "Swoole http server is started\n";
});

$server->start();

?>