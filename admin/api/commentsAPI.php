<?php
/**
 * Created by PhpStorm.
 * User: LiLu
 * Date: 2018/7/25
 * Time: 15:22
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/functions.php";
// 响应客户端提交的Ajax请求
$current_page = empty($_GET["cp"]) ? 1 : intval($_GET["cp"]);
$page_size = 6;
$offset = ($current_page - 1) * $page_size;
// 查询所有评论信息
$sql = sprintf("select
	comments.*,
	posts.title as post_title
from comments
INNER JOIN posts ON comments.post_id = posts.id
ORDER BY comment_time DESC
LIMIT %d,%d;",$offset,$page_size);
$comments_data = fetch_all($sql);

// 查询总评论数
$comments_count = fetch_one("select count(1) as count from comments INNER JOIN posts ON comments.post_id = posts.id")["count"];
// 将数组序列化
$json = json_encode(array(
    "totalPage" => ceil($comments_count/$page_size),
    "comments" => $comments_data
));
// 设置响应的响应体类型为json
header("Content-Type: application/json");
// 响应输出
echo $json;