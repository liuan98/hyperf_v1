<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;
//todo:首页22
Router::addRoute(['GET', 'POST', 'HEAD'], '/index', 'App\Controller\IndexController@index');
Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@dist');
Router::addRoute(['GET', 'POST', 'HEAD'], '/back', 'App\Controller\IndexController@back');
Router::addRoute(['GET','POST', 'HEAD'], '/ossUpload', 'App\Controller\UploadController@ossUpload');//上传图片
//前台
//todo:登陆
Router::addRoute(['GET','POST', 'HEAD'], '/indexImg', 'App\Controller\IndexController@indexImg');//登陆logo
Router::addRoute(['GET','POST', 'HEAD'], '/login', 'App\Controller\ApidemoController@login');//登陆
Router::addRoute(['GET','POST', 'HEAD'], '/register', 'App\Controller\IndexController@register');//注册
Router::addRoute(['GET','POST', 'HEAD'], '/checkToken', 'App\Controller\IndexController@checktoken');//token
Router::addRoute(['GET','POST', 'HEAD'], '/cate', 'App\Controller\IndexController@cate');//类别
Router::addRoute(['GET','POST', 'HEAD'], '/detail', 'App\Controller\IndexController@detail');//页面
//走中间件验证token
Router::addGroup('/',function (){
    Router::addRoute(['GET','POST', 'HEAD'], 'list', 'App\Controller\IndexController@list');//首页产品列表
    Router::addRoute(['GET','POST', 'HEAD'], 'logout', 'App\Controller\IndexController@logout');//退出登陆
    Router::addRoute(['GET','POST', 'HEAD'], 'chgpwd', 'App\Controller\IndexController@chgpwd');//修改密码
}, ['middleware' => [\App\Middleware\Auth\FooMiddleware::class]]);

//后台
//todo:登陆
Router::addRoute(['GET','POST', 'HEAD'], '/backLogin', 'App\Controller\BackController@login');//登陆
Router::addRoute(['GET','POST', 'HEAD'], '/tokenInfo', 'App\Controller\BackController@tokenInfo');//根据token获取用户信息
//走中间件验证token
Router::addGroup('/',function (){
    Router::addRoute(['GET','POST', 'HEAD'], 'backLogout', 'App\Controller\BackController@logout');//退出登陆
    Router::addRoute(['GET','POST', 'HEAD'], 'backChgpwd', 'App\Controller\BackController@chgpwd');//修改密码
    Router::addRoute(['GET','POST', 'HEAD'], 'userInfo', 'App\Controller\BackController@userInfo');//用户信息

    Router::addRoute(['GET','POST', 'HEAD'], 'itemList', 'App\Controller\BackController@itemList');//页面管理列表
    Router::addRoute(['GET','POST', 'HEAD'], 'itemAdd', 'App\Controller\BackController@itemAdd');//页面管理新增
    Router::addRoute(['GET','POST', 'HEAD'], 'itemInfo', 'App\Controller\BackController@itemInfo');//页面管理信息
    Router::addRoute(['GET','POST', 'HEAD'], 'itemDel', 'App\Controller\BackController@itemDel');//页面删除
    Router::addRoute(['GET','POST', 'HEAD'], 'itemEdit', 'App\Controller\BackController@itemEdit');//页面编辑
    Router::addRoute(['GET','POST', 'HEAD'], 'logoEdit', 'App\Controller\BackController@logoEdit');//登陆页背景保存

    Router::addRoute(['GET','POST', 'HEAD'], 'upload', 'App\Controller\UploadController@upload');//上传图片

    //todo:分类
    Router::addRoute(['GET','POST', 'HEAD'], 'cateList', 'App\Controller\BackController@cateList');//分类列表
    Router::addRoute(['GET','POST', 'HEAD'], 'cateAdd', 'App\Controller\BackController@cateAdd');//分类添加
    Router::addRoute(['GET','POST', 'HEAD'], 'cateEdit', 'App\Controller\BackController@cateEdit');//分类编辑
    Router::addRoute(['GET','POST', 'HEAD'], 'cateDel', 'App\Controller\BackController@cateDel');//分类删除

    //todo:产品
    Router::addRoute(['GET','POST', 'HEAD'], 'productList', 'App\Controller\BackController@productList');//分类列表
    Router::addRoute(['GET','POST', 'HEAD'], 'productAdd', 'App\Controller\BackController@productAdd');//分类添加
    Router::addRoute(['GET','POST', 'HEAD'], 'productInfo', 'App\Controller\BackController@productInfo');//产品信息
    Router::addRoute(['GET','POST', 'HEAD'], 'productEdit', 'App\Controller\BackController@productEdit');//产品编辑
    Router::addRoute(['GET','POST', 'HEAD'], 'productDel', 'App\Controller\BackController@productDel');//产品删除

    //todo:角色
    Router::addRoute(['GET','POST', 'HEAD'], 'authList', 'App\Controller\BackController@authList');//角色列表
    Router::addRoute(['GET','POST', 'HEAD'], 'authAdd', 'App\Controller\BackController@authAdd');//角色添加
    Router::addRoute(['GET','POST', 'HEAD'], 'authInfo', 'App\Controller\BackController@authInfo');//角色信息
    Router::addRoute(['GET','POST', 'HEAD'], 'authEdit', 'App\Controller\BackController@authEdit');//角色编辑
    Router::addRoute(['GET','POST', 'HEAD'], 'authDel', 'App\Controller\BackController@authDel');//角色删除

    //todo:用户
    Router::addRoute(['GET','POST', 'HEAD'], 'consumerList', 'App\Controller\BackController@consumerList');//用户列表
    Router::addRoute(['GET','POST', 'HEAD'], 'consumerAdd', 'App\Controller\BackController@consumerAdd');//用户添加
    Router::addRoute(['GET','POST', 'HEAD'], 'consumerInfo', 'App\Controller\BackController@consumerInfo');//用户管理信息
    Router::addRoute(['GET','POST', 'HEAD'], 'consumerEdit', 'App\Controller\BackController@consumerEdit');//用户编辑
    Router::addRoute(['GET','POST', 'HEAD'], 'consumerDel', 'App\Controller\BackController@consumerDel');//用户删除

}, ['middleware' => [\App\Middleware\Auth\BackMiddleware::class]]);

//中间键例：
//Router::addRoute(['GET','POST', 'HEAD'], '/contact',[\App\Controller\DdController::class, 'contact'], ['middleware' => [FooMiddleware::class]]);
Router::addRoute(['GET','POST', 'HEAD'], '/excelTest', 'App\Controller\UploadController@test');//测试导入excel中有图片的情况
Router::addRoute(['GET','POST', 'HEAD'], '/UdpClient', 'App\Controller\UdpController@UdpClient');//测试UDP客户端连接
Router::addRoute(['GET','POST', 'HEAD'], '/tlvTest', 'App\Controller\UdpController@tlvTest');//测试应答
Router::addRoute(['GET','POST', 'HEAD'], '/tlvSer', 'App\Controller\UdpController@tlvSer');//测试拼接数据