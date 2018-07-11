<?php
/**
 * Created by PhpStorm.
 * User: LiLu
 * Date: 2018/7/11
 * Time: 9:24
 */
require_once '../functions.php';
if (empty($_GET['id'])){
    exit("缺少必要参数");
}
// 删除单个防注入
// $id = (int)$_GET['id'];
// 删除多个，传入字符串 1,2,3
$id = $_GET['id'];
// 分割后判断每个字符是否为数字，防注入
// 使用软删除
execute("update categories set category_status = 0 where id in ({$id});");
header("Location: /admin/categories.php");