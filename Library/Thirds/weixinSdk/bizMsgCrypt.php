<?php

include_once "bizMsgCrypt/WXBizMsgCrypt.php";

// 假设企业号在公众平台上设置的参数如下
$encodingAesKey = "Ru37BRJwMWvUH6jOfRUi9DbfRchv7FtEf62DkVKbrwY";
$token = "smrt";
$corpId = "wx6c13a09936584ffd";

$sVerifyMsgSig = $_GET['msg_signature'];
$sVerifyTimeStamp = $_GET['timestamp'];
$sVerifyNonce = $_GET['nonce'];
$sVerifyEchoStr = $_GET['echostr'];
$sEchoStr = "";

$wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
$errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);

if ($errCode == 0) {
	// 验证URL成功，将sEchoStr返回
    echo $sEchoStr;
} else {
	print("ERR: " . $errCode . "\n\n");
}
