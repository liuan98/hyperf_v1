<?php

namespace App\Sdk;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
 
/**
 * 阿里信息发送类
 */
class Aliyunsms
{
  /**
   * 发送短信验证码
   */
  public function sendCode($config,$phone,$code)
  {
    $param = [
      'code' => $code,
    ];
    AlibabaCloud::accessKeyClient($config['accessKeyId'], $config['accessSecret'])
      ->regionId($config['regionId'])
      ->asGlobalClient();
 
    try {
      $result = AlibabaCloud::rpcRequest()
        ->product('Dysmsapi')
        ->version('2017-05-25')
        ->action('SendSms')
        ->method('POST')
        ->options([
          'query' => [
            'PhoneNumbers' => $phone,
            'SignName' => $config['SignName'],
            'TemplateCode' => $config['TemplateCode'],
            'TemplateParam' => json_encode($param)
          ],
        ])
        ->request();
      return $result->toArray();
    } catch (ClientException $e) {
      echo $e->getErrorMessage() . PHP_EOL;
    } catch (ServerException $e) {
      echo $e->getErrorMessage() . PHP_EOL;
    }
  }
}