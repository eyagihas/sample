<?php

namespace Services;

class Mail
{
  public static $operationAddress = "eri.yagihashi@gmail.com";
  public static $operationCcAddress = "";
  public static $siteAddress = "eri.yagihashi@gmail.co";
  public static $siteName = "矯正歯科ネットプラス";

  //ヘッダ情報生成
  public static function getHeader($fromName, $from, $return, $reply, $cc = "", $bcc = "")
  {
    $fromName = mb_encode_mimeheader($fromName);
    $str = "From: ".$fromName."<".$from.">"."\r\n";
    $str.= ($cc !== "") ? "Cc: ".$cc."\r\n" : "";
    $str.= ($bcc !== "") ? "Bcc: ".$bcc."\r\n" : "";
    $str.= "Return-Path: ".$return."\r\n";
    $str.= "X-Priority: 1\r\n";
    $str.= "Reply-To: ".$reply."\r\n";
    return $str;
  }

  //メール送信
  public static function sendMail($to, $header, $subject, $body, $encode)
  {
    mb_language("Japanese");
    mb_internal_encoding($encode);
    mb_send_mail($to, $subject, $body, $header);
    return true;
  }

  //サーバー情報取得
  public static function getServerInfo()
  {
    $str = "--------------------------------------------\n";
    $str.= "[日時]\n" . date("Y/m/d H:i") . "\n\n";
    $str.= "[リファラー]\n" . filter_input(INPUT_SERVER, 'HTTP_REFERER') . "\n\n";
    $str.= "[ポート]\n" . filter_input(INPUT_SERVER, 'SERVER_PORT') . "\n\n";
    $str.= "[リモートアドレス]\n" . filter_input(INPUT_SERVER, 'REMOTE_ADDR') . "\n\n";
    $str.= "[ユーザーエージェント]\n" . filter_input(INPUT_SERVER, 'HTTP_USER_AGENT') . "\n\n";
    $str.= "[ホスト名]\n" . @gethostbyaddr(filter_input(INPUT_SERVER, 'REMOTE_ADDR')) . "\n";
    $str.= "--------------------------------------------\n";
    return $str;

  }

}