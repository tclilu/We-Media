<?php
/**
 * Created by PhpStorm.
 * User: LiLu
 * Date: 2018/7/11
 * Time: 16:35
 */
require_once '../functions.php';
if (!isset($_GET['id'])){
    exit("缺少必要参数");
}
// 接收参数
$id = $_GET['id'];
// 软删除
// 更新操作 更新多条数据
execute("update users set user_status = 'forbidden' where id in ({$id});");
header("Location: /admin/users.php");