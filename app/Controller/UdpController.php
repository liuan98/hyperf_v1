<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 *
 * excel导出/导入
 */

namespace App\Controller;


use App\Sdk\TlvService;

class UdpController extends AbstractController
{
    /**
     * 测试udp
     * 47.101.134.23
     */
    public function UdpClient()
    {
        $client = new \Swoole\Client(SWOOLE_SOCK_UDP);
        $client->connect('47.101.134.23', 19699, 0.9);
        //客户端上行发送数据
        $Tlv = new TlvService();
        //发送数据
//        $str = '05af1100ff010d000000090077653a313233343536';
//        $sendData = @hex2bin($str);//十六进制数转化为ASCII字符
        $pack = [
            //头部顺序反向
            ["05AF", $Tlv->LengthTohex(strlen('05AFFF010000we:123456')),''],
            //指令顺序固定 todo:如果长度是固定值，直接填进去即可["05AF", $Tlv->LengthTohex(12),'']
            ["FF01", $Tlv->LengthTohex(strlen('01FF0000we:123456')),''],
            //指令顺序反向，true是代表不是字符串需要十进制转十六进制 ，不传代表字符串或者空["0000", $Tlv->LengthTohex(strlen('1011')),'1011',true]
            ["0000", $Tlv->LengthTohex(strlen('we:123456')),'we:123456'],
        ];
        $sendData = $Tlv->Write($pack);
        var_dump(['发送数据' => $sendData]);
        //$sendData = $Tlv->Write([["0x05AF".strlen('0x05AF')."0x01FF".strlen('0x01FF'),strlen('we:123456'), 'we:123456']]);
        $client->send($sendData);
        var_dump(['发送数据2' => bin2hex($sendData)]);
        //接收数据
        $ret = $client->recv();
        var_dump(['接收数据' => $ret]);
        $recData = bin2hex($ret);//ASCII字符转十六进制
        var_dump(['接收数据转十六进制' => $recData]);
        $val = $Tlv->substrValue($recData,[4]);//取十六进制中的value
        var_dump(['获取到的value' => $val]);
        //todo:然后直接对返回$val的进行进制转化就可以了
        $res = $Tlv->decTohex(substr($val[0],0,4));//十六进制转化为十进制得到结果
        return success($res);
    }

    /**
     * @return mixed
     * 测试
     */
    public function tlvTest(){
        $Tlv = new TlvService();
        $str = '05af1600020112000000040010b1c50001000200000002000000';
        //只取应答value
        //$str：代表返回的十六进制
        //[4,2,4] 代表每一个value的值的长度
        $val = $Tlv->substrValue($str,[4,2,4]);
        return $val;
    }

    /**
     * @return mixed
     * 测试
     */
    public function tlvSer(){
        $client = new \Swoole\Client(SWOOLE_SOCK_UDP);
        $client->connect('47.101.134.23', 19699, 0.9);
        //客户端上行发送数据
        $Tlv = new TlvService();
        //发送数据
//        $str = '05af1100ff010d000000090077653a313233343536';
//        $sendData = @hex2bin($str);//十六进制数转化为ASCII字符
        $pack = [
            //头部顺序反向
            ["05AF", $Tlv->LengthTohex(2+1+8+1+strlen('控制柜')+4+4+strlen('0105')),''],
            //指令顺序固定 todo:如果长度是固定值，直接填进去即可["05AF", $Tlv->LengthTohex(12),'']
            ["0105", $Tlv->LengthTohex(2+1+8+1+strlen('控制柜')+4+4),''],
            //指令顺序反向，true是代表不是字符串需要十进制转十六进制 ，不传代表字符串或者空["0000", $Tlv->LengthTohex(strlen('1011')),'1011',true]
            ["0000", $Tlv->LengthTohex(4),'1011',true],
            ["0100", $Tlv->LengthTohex(4),'1004',true],
            ["0200", $Tlv->LengthTohex(strlen('控制柜')),'控制柜'],
            ["0300", $Tlv->LengthTohex(1),'1',true],
            ["0400", $Tlv->LengthTohex(8),'73.e4be3,1c.b0882'],
            ["0500", $Tlv->LengthTohex(1),'1',true],
            ["0600", $Tlv->LengthTohex(2),'0104'],
        ];
        $sendData = $Tlv->Write($pack);
        var_dump(['发送数据' => $sendData]);
        //$sendData = $Tlv->Write([["0x05AF".strlen('0x05AF')."0x01FF".strlen('0x01FF'),strlen('we:123456'), 'we:123456']]);
        $client->send($sendData);
        var_dump(['发送数据2' => bin2hex($sendData)]);
        //接收数据
        $ret = $client->recv();
        var_dump(['接收数据' => $ret]);
        $recData = bin2hex($ret);//ASCII字符转十六进制
        var_dump(['接收数据转十六进制' => $recData]);
        //$val = $Tlv->substrValue($recData,[4]);//取十六进制中的value
        //var_dump(['获取到的value' => $val]);
    }
}

