<?php
/**
 * Created by PhpStorm.
 * User: LiLu
 * Date: 2018/7/10
 * Time: 11:42
 * 获取用户头像的接口
 */
// 引入配置文件
require_once '../../config.php';
// 接收get方式提交的email参数
if (empty($_GET['email'])){
    exit("缺少必要参数！");
}
$email = $_GET['email'];
// 连接数据库
$conn = @mysqli_connect(DB_HOST,DB_USER,DB_PWD,DB_NAME) or die("数据库连接失败！");
// 查询邮箱对应头像
$query_result = mysqli_query($conn,"select avator from users where email='{$email}' limit 1;") or die("查询失败");
$response = mysqli_fetch_assoc($query_result);
// 响应结果
echo $response['avator'];