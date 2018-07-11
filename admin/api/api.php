<?php
/**
 * Created by PhpStorm.
 * User: LiLu
 * Date: 2018/7/10
 * Time: 11:42
 * 获取用户头像的接口
 */
// 引入配置文件
require_once '../../functions.php';
// 接收get方式提交的email参数
if (empty($_GET['email']) || empty($_GET['field'])){
    exit("缺少必要参数！");
}
$email = $_GET['email'];
$field = $_GET['field'];
$response = fetch_one("select * from users where email='{$email}' limit 1;");
// 响应结果
if ('avator' === $field){
    echo $response['avator'];
} elseif ('email' === $field){
    echo $response['email'];
}