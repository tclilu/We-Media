<?php
/**
 * Created by PhpStorm.
 * User: LiLu
 * Date: 2018/7/26
 * Time: 11:20
 */
require_once $_SERVER["DOCUMENT_ROOT"] . '/functions.php';
if (empty($_GET['id'])){
    exit("缺少必要参数");
}
// 删除单个防注入
// $id = (int)$_GET['id'];
// 删除多个，传入字符串 1,2,3
$id = $_GET['id'];
// 分割后判断每个字符是否为数字，防注入
// 使用软删除
$result = execute("update comments set comment_status = 'approved' where id in ({$id});");
header("Content-Type: application/json");
echo json_encode($result > 0);