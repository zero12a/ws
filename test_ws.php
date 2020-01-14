<?php
header("Content-Type: text/html; charset=UTF-8");
header("Cache-Control:no-cache");
header("Pragma:no-cache");

//로그인 검사
$CFG = require_once("../common/include/incConfig.php");

require_once "../common/include/incUtil.php";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>chat</title>

	<meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">


	<!--jquery / json-->
	<script src="<?=$CFG["CFG_URL_LIBS_ROOT"]?>lib/jquery/jquery-1.12.4.min.js"></script>
	<script src="<?=$CFG["CFG_URL_LIBS_ROOT"]?>lib/json2.min.js"></script>

    <!--공통-->
    <script src="/common/common.js?<?=getRndVal(10)?>" type="text/javascript" charset="utf-8"></script>    
    <link href="/common/common.css?<?=getRndVal(10)?>" rel="stylesheet" type="text/css" />

    <script>
    var ws;

    $(document).ready(function(){
        $("#msgHistory").val("");//지우기



        $("#btnSend").click(function(){
            sendMsg();
        });

        $("#btnEnter").click(function(){
            makeSocket();
        });

        $("#txtMsg").keydown(function(key) {
            if (key.keyCode == 13) {
                //엔터키 입력 시 작업할 내용
                sendMsg();
            }
        });
    });

    
    function makeSocket(){
        ws = new WebSocket("ws://localhost:8050/?userid=" + $("#txtUserid").val() + "&nickname=" + $("#txtNickname").val()); // ws와 wss(ws에 TLS 패킷 암호화 붙음 )

        ws.onmessage = function(e){  addMsg(e.data); }
        ws.onopen = function(e){ addMsg('{"msg":"connected."}'); }
        ws.onclose = function(e){ addMsg('{"msg":"closed."}'); }  
    }

    function addMsg(tData){
        alog(tData);
        objJson = JSON.parse(tData);
        $("#msgHistory").val($("#msgHistory").val() + "\n" + objJson.msg);
    }
    function sendMsg(){
        ws.send($("#txtMsg").val());
        $("#txtMsg").val("");
        $("#txtMsg").focus();
    }



    </script>
</head>
<body>
<form onsubmit="return false;">
<textarea id="msgHistory" name="msgHistory" style="width:100%;height:200px"></textarea><br>
<input type="text" id="txtUserid" name="txtUserid" value="testid" style="width:60px;">
<input type="text" id="txtNickname" name="txtNickname" value="천상신양" style="width:60px;">
<input type="text" id="txtMsg" name="txtMsg" style="width:300px;">
<input type="button" id="btnEnter" name="btnEnter" value="입장">
<input type="button" id="btnSend" name="btnSend" value="보내기">
</form>
</body>
</html>