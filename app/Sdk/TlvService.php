<?php

namespace App\Sdk;

/**
 *
 * TLV包解析类
 */
class TlvService
{

    private $buffer;
    private $t_len = 4;                //T长度
    private $l_len = 4;                //L长度
    private $buf_len = 0;          //字节流长度
    private $buf_array = array();

    /**
     * 构造函数
     */
    function __construct()
    {
    }

    /**
     * 解析数据
     *
     * @param byte $buffer 二进制流数据
     * @param  $IsArray
     * @return array
     */
    function Read($buffer, $IsArray = false)
    {
        $this->buffer = $buffer;
        $this->buf_len = strlen($this->buffer);

        //清空数组
        if (isset($this->buf_array)) {
            unset($this->buf_array);
            $this->buf_array = array();
        }

        $i = 0;
        while ($i < $this->buf_len) {
            //获取TGA
            $t = $this->getLength($i, $this->t_len);
            if ($this->toHex($t) == "0xffffffff") break;
            $i += $this->t_len;
            //获取Length
            $l = $this->getLength($i, $this->l_len);
            $i += $this->l_len;
            //获取Value
            $v = substr($this->buffer, $i, $l);
            $i += $l;

            if ($IsArray) {
                $this->buf_array[$this->toHex($t)] = array($this->toHex($t), $l, $v);
            } else {
                array_push($this->buf_array, array($this->toHex($t), $l, $v));
            }
        }
        return $this->buf_array;
    }

    //将数组转换二进制数据
    function Write($arrdata)
    {
        $msg = '';
        $lenth = 0;
        for ($i = 0; $i < count($arrdata); $i++) {
            $msg .= $this->Pack("H*", $arrdata[$i][0]);
//            $msg .= $arrdata[$i][0];
            $msg .= $this->Pack("H*", $arrdata[$i][1]);
//            $msg .= $arrdata[$i][1];
            if($arrdata[$i][2]) {
                $msg .= $this->Pack("H*", (isset($arrdata[$i][3]) && $arrdata[$i][3])?$this->LengthTohex($arrdata[$i][2]):bin2hex($arrdata[$i][2]));
            }
        }
        return $msg;
    }

    /**
     * 长度转16进制
     */
    function LengthTohex($str)
    {
        $strg = dechex($str);//十进制转十六进制
        switch (strlen($strg)) {
            case 1:
                $strhex = "0" . $strg . "00";
                break;
            case 2:
                $strhex = "" . $strg . "00";
                break;
            case 3:
                $strhex = "" . substr($strg,1,2) . "0" .substr($strg,0,1);
                break;
            case 4:
                $strhex = "" . substr($strg,2,2).substr($strg,0,2);
                break;
            default:
        }
        return $strhex;
    }
    /**
     * @param $key
     * @return mixed
     * 截取返回的value
     * $str 返回的十六进制
     * $arleg 所有value值的长度数组
     */
    function substrValue($str,$arleg){
        $str = substr($str,16);
        foreach ($arleg as $key=>$value){
            $string[] = substr($str,8,$value*2);
            $str = substr($str,8+$value*2);
        }
        return $string;
    }
    /**
     * 返回过来的十六进制转十进制
     */
    function decTohex($str)
    {
        switch (strlen($str)) {
            case 1:
                $strhex = "0" . $str . "00";
                break;
            case 2:
                $strhex = "" . $str . "00";
                break;
            case 3:
                $strhex = "" . substr($str,1,2) . "0" .substr($str,0,1);
                break;
            case 4:
                $strhex = "" . substr($str,2,2).substr($str,0,2);
                break;
            default:
        }
        return hexdec($strhex);
    }

    //获取值
    function getValue($key)
    {
        return $this->buf_array[$key][2];
    }

    //转换成十六进制
    function toHex($value)
    {
        return "0x" . dechex($value);
    }

    //压包
    function Pack($format, $data)
    {
        return pack($format, $data);
    }

    //解包
    function Unpack($format, $data)
    {
        return unpack($format, $data);
    }

    function getLength($start, $len)
    {
        return $this->Unpack('N*', substr($this->buffer, $start, $len));
    }

    //清楚所有数据
    function Clear()
    {
        if (isset($this->buffer)) {
            unset($this->buffer);
        }
        $this->buf_len = 0;
    }
    /**
     * 将字符串转换成二进制
     * @param type $str
     * @return type
     */
    function StrToBin($str){
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //2.unpack字符
        foreach($arr as &$v){
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);
            unset($temp);
        }

        return join(' ',$arr);
    }
    public static function getBytes($string) {
        $bytes = array();
        for($i = 0; $i < strlen($string); $i++){
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }
}

/**
 * 客户端上行包
 */
class PacketData extends TlvService
{
    function GetPack($data)
    {

        $tag = "05AF";

        $tag2 = "FF02";

        $value = TlvService::Write($data);

        $ret1 = array(array($tag2,strlen($value),$value));var_dump($ret1);

        $value1 = TlvService::Write($ret1);

        $ret2 = array(array($tag,12,$value1));var_dump($ret2);

        return @hex2bin(TlvService::Write($ret2));
    }

    function Clear()
    {

        if (isset($this->Pack_arry)) {

            unset($this->Pack_arry);

        }

    }
}

?>