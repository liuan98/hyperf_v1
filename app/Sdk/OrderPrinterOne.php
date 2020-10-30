<?php

/**
 * 商品订单小票易连云打印机api（2）
 * @author twx
 * @date 2020-06-23
 */

namespace App\Sdk;

use App\Model\Goods;
use App\Model\ConsumeEffects;
use App\Model\Menber;
use App\Model\Order;
use App\Model\OrderGoods;
use App\Model\Present;
use App\Model\PresentOrder;

class OrderPrinterOne
{
    private $apiKey = 'c3ebf67fa8098c5cf37bd040e5703a4e6a759f46';

    private $msign = '594767181451';

    private $partner = '43219624';

    private $machine_code = '4004675873';

    /**
     * 生成签名sign
     * @param  array $params 参数
     * @param  string $apiKey API密钥
     * @param  string $msign 打印机密钥
     * @return   string sign
     */
    public function generateSign($params, $apiKey, $msign)
    {
        //所有请求参数按照字母先后顺序排
        ksort($params);
        //定义字符串开始所包括的字符串
        $stringToBeSigned = $apiKey;
        //把所有参数名和参数值串在一起
        foreach ($params as $k => $v) {
            $stringToBeSigned .= urldecode($k . $v);
        }
        unset($k, $v);
        //定义字符串结尾所包括的字符串
        $stringToBeSigned .= $msign;
        //使用MD5进行加密，再转化成大写
        return strtoupper(md5($stringToBeSigned));
    }
    /**
     * 生成字符串参数
     * @param array $param 参数
     * @return  string        参数字符串
     */
    public function getStr($param)
    {
        $str = '';
        foreach ($param as $key => $value) {
            $str = $str . $key . '=' . $value . '&';
        }
        $str = rtrim($str, '&');
        return $str;
    }
    /**
     * 打印普通订单接口
     * @param  int $partner     用户ID
     * @param  string $machine_code 打印机终端号
     * @param  string $content      打印内容
     * @param  string $apiKey       API密钥
     * @param  string $msign       打印机密钥
     */
    public function  order_action_print($order_id)
    {
        $param = array(
            "partner" => $this->partner,
            'machine_code' => $this->machine_code,
            'time' => time(),
        );
        //获取签名
        $param['sign'] = $this->generateSign($param, $this->apiKey, $this->msign);

        $order = Order::where('id', $order_id)->with('staff')->first();
        $goods = OrderGoods::where('order_id', $order->id)->get();
        if (empty($order)) {
            return '订单不存在';
        }
        $staff_name =  $order->staff->name;

        if (empty($order->menber_id)) {
            if ($order->pay_way == 1) {
                $way = '现金';
            } elseif ($order->pay_way == 2) {
                $way = '微信';
            } elseif ($order->pay_way == 3) {
                $way = '支付宝';
            }
        } else {
            if ($order->is_type == 1) {
                $way = '余额';
            } else {
                $way = '积分';
            }
        }

        $content = "<FS2><center>**桌号：$order->table_number**</center></FS2>";
        $content .= "<FS2>订单时间:" . $order->created_at . "\n</FS2>";
        $content .= "<FS2>订单编号:$order->order_sn\n</FS2>";
        $content .= "<FS2>员工姓名:$staff_name\n</FS2>";
        $content .= "<FS2>支付方式:$way\n</FS2>";
        $content .= str_repeat('*', 7) . "商品" . str_repeat("*", 7) . "\n";
        $content .= "<FS2><table>";
        foreach ($goods as $val) {
            $name = Goods::where('id', $val->goods_id)->value('name');
            $content .= "<tr><td>$name</td><td>x$val->goods_num</td><td>￥$val->goods_price</td></tr>";
        }
        $content .= "</table></FS2>";
        $content .= "<FS2>支付:￥" . $order->total_price . "\n</FS2>";
        $content .= "<FS2><center>**#1 完**</center></FS2>";

        $param['content'] = $content;
        $str = $this->getStr($param);
        $status = $this->sendCmd('http://open.10ss.net:8888', $str);
        $status = json_decode($status, true);
        if ($status['state'] == 1) {
            $order->update([
                'is_print' => 1
            ]);
        }
        return $status['state'];
    }

    /**
     * 打印礼物订单接口
     * @param  int $partner     用户ID
     * @param  string $machine_code 打印机终端号
     * @param  string $content      打印内容
     * @param  string $apiKey       API密钥
     * @param  string $msign       打印机密钥
     */
    public function  present_action_print($order_id)
    {
        $param = array(
            "partner" => $this->partner,
            'machine_code' => $this->machine_code,
            'time' => time(),
        );
        //获取签名
        $param['sign'] = $this->generateSign($param, $this->apiKey, $this->msign);

        $order = PresentOrder::where('id', $order_id)->with('staff', 'menber', 'woman')->first();
        $menber_name =  $order->menber->name;
        $woman_name =  $order->woman->name;
        $staff_name =  $order->staff->name;

        if (empty($order)) {
            return '订单不存在';
        }

        $content = "<FS2><center>**打榜礼物订单**</center></FS2>";
        $content .= "订单时间:" . $order->created_at . "\n";
        $content .= "订单编号:$order->order_sn\n";
        $content .= "员工:$staff_name\n";
        $content .= "赠送人:$menber_name\n";
        $content .= "收礼佳人:$woman_name\n";
        $content .= str_repeat('*', 14) . "商品" . str_repeat("*", 14);
        $content .= "<table>";
        $present = Present::where('id', $order->present_id)->first();
        $content .= "<tr><td>$present->name</td><td>x1</td><td>￥$order->price</td></tr>";
        $content .= "</table>";
        $content .= "支付:￥" . $order->price . "\n";
        $content .= "<FS2><center>**#1 完**</center></FS2>";

        $param['content'] = $content;
        $str = $this->getStr($param);
        $status = $this->sendCmd('http://open.10ss.net:8888', $str);
        $status = json_decode($status, true);
        if ($status['state'] == 1) {
            $order->update([
                'is_print' => 1
            ]);
        }
        return $status['state'];
    }
    /**
     * 打印单笔消费赠品接口
     * @param  int $partner     用户ID
     * @param  string $machine_code 打印机终端号
     * @param  string $content      打印内容
     * @param  string $apiKey       API密钥
     * @param  string $msign       打印机密钥
     */
    public function  giveaway($order_id)
    {
        $param = array(
            "partner" => $this->partner,
            'machine_code' => $this->machine_code,
            'time' => time(),
        );
        //获取签名
        $param['sign'] = $this->generateSign($param, $this->apiKey, $this->msign);

        $order = ConsumeEffects::where('id', $order_id)->first();

        if (empty($order)) {
            return '订单不存在';
        }

        $content = "<FS2><center>**赠品订单**</center></FS2>";
        $content .= "触发时间:" . $order->created_at . "\n";
        $content .= "触发会员:$order->name\n";
        $content .= "触发特效:$order->effects_name\n";
        $content .= "会员电话:$order->mobile\n";
        $content .= "<FS2><center>**#1 完**</center></FS2>";

        $param['content'] = $content;
        $str = $this->getStr($param);
        $status = $this->sendCmd('http://open.10ss.net:8888', $str);
        $status = json_decode($status, true);
    }
    /**
     *  添加打印机
     * @param  int $partner     用户ID1		
     * @param  string $machine_code 打印机终端号
     * @param  string $username     用户名
     * @param  string $printname    打印机名称
     * @param  string $mobilephone  打印机卡号
     * @param  string $apiKey       API密钥
     * @param  string $msign       打印机密钥
     */
    public function action_addprint($partner, $machine_code, $username, $printname, $mobilephone, $apiKey, $msign)
    {
        $param = array(
            'partner' => $partner,
            'machine_code' => $machine_code,
            'username' => $username,
            'printname' => $printname,
            'mobilephone' => $mobilephone,
        );
        $param['sign'] = $this->generateSign($param, $apiKey, $msign);
        $param['msign'] = $msign;
        $str = $this->getStr($param);
        echo $this->sendCmd('http://open.10ss.net:8888/addprint.php', $str);
    }
    /**
     * 删除打印机
     * @param  int $partner      用户ID
     * @param  string $machine_code 打印机终端号
     * @param  string $apiKey       API密钥
     * @param  string $msign        打印机密钥
     */
    public function action_removeprinter($partner, $machine_code, $apiKey, $msign)
    {
        $param = array(
            'partner' => $partner,
            'machine_code' => $machine_code,
        );
        $param['sign'] = $this->generateSign($param, $apiKey, $msign);
        $str = $this->getStr($param);
        echo $this->sendCmd('http://open.10ss.net:8888/removeprint.php', $str);
    }
    /**
     * 发起请求
     * @param  string $url  请求地址
     * @param  string $data 请求数据包
     * @return   string      请求返回数据
     */
    public function sendCmd($url, $data)
    {
        $curl = curl_init(); // 启动一个CURL会话      
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址                  
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测    
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在      
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //解决数据包大不能提交     
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转      
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer      
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求      
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包      
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循     
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容      
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回 

        $tmpInfo = curl_exec($curl); // 执行操作      
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话      
        return $tmpInfo; // 返回数据      
    }
}
