<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
get_now_user();

// 当前页
$current_page = empty($_GET["p"]) ? 1 : (int)$_GET["p"];
if ($current_page < 1){
    exit("参数错误");
}
// 每页的大小
$page_size = 7;
// 每页开始的位置
$offset = ($current_page - 1) * $page_size;

// 筛选数据 表单提交的参数
$where = "1=1";
$url = "";
$where .= (empty($_GET["category"]) || $_GET["category"] === 'all_category') ? '' : " and posts.c_id=" . $_GET["category"];
$where .= (empty($_GET["posts_status"]) || $_GET["posts_status"] === 'all_status') ? '' : " and posts.post_status='" . $_GET["posts_status"] . "'";
$url .= empty($_GET["category"]) ? '' : "&category=" . $_GET["category"];
$url .= empty($_GET["posts_status"]) ? '' : "&posts_status=" . $_GET["posts_status"];
// 取出每页的文章数据
$posts_data = fetch_all("select 
	posts.id,
	posts.title,
	users.nick_name,
	categories.category_name,
	posts.created_time,
	posts.post_status
FROM posts
INNER JOIN categories ON posts.c_id=categories.id
INNER JOIN users ON posts.user_id=users.id
WHERE {$where}
ORDER BY created_time DESC
LIMIT {$offset},{$page_size};");
if ($posts_data == null){
    $empty_data = "<tr><td class='text-center' colspan='7'>~暂无相关文章数据~</td></tr>";
}
// 总页数
$posts_count = fetch_one("select count(1) as count from posts INNER JOIN categories ON posts.c_id=categories.id INNER JOIN users ON posts.user_id=users.id WHERE {$where};")["count"];
// ceil函数：向上取整
$total_page = ceil($posts_count / $page_size) == 0 ? 1 : ceil($posts_count / $page_size);
// 处理错误参数
if ($current_page > $total_page){
    exit("参数错误");
}
// 计算分页页码
$visible = 5;
// 处理边界情况
$begin = $current_page - ($visible - 1)*0.5 < 1 ? 1 : $current_page - ($visible - 1)*0.5;
$end = ($begin + ($visible - 1));
if ($end > $total_page){
    $begin = $total_page > $visible ? $total_page - ($visible - 1) : 1;
    $end = $total_page;
}
/**
 * 文章状态格式转换
 * @param $post_status 文章状态
 * @return string 对应内容
 */
function format_status($post_status){
    $base_status = array(
        "drafted" => "草稿",
        "published" => "已发布",
        "trashed" => "回收站"
    );
    return isset($base_status[$post_status]) ? $base_status[$post_status] : "未知";
}

/**
 * 时间格式转换
 * strtotime函数
 * 将任何字符串的日期时间描述解析为 Unix 时间戳
 * @param $created_time
 * @return false|string
 */
function format_created_time($created_time){
    return date('Y年m月d日<b\r>H:i:s',strtotime($created_time));
}

// 查询所有分类
$categories_data = fetch_all("select id,category_name from categories;");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Posts &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
    <?php include "./common/navbar.php";?>
    <div class="container-fluid">
      <div class="page-title">
        <h1>所有文章</h1>
        <a href="post-add.php" class="btn btn-primary btn-xs">写文章</a>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <a class="btn btn-danger btn-sm" href="javascript:;" style="display: none">批量删除</a>
        <form class="form-inline" method="get" action="<?php echo $_SERVER["PHP_SELF"];?>">
          <select name="category" class="form-control input-sm category">
              <option value="all_category">所有分类</option>
              <?php foreach ($categories_data as $item) {?>
                  <option <?php echo isset($_GET["category"]) && $_GET["category"] === $item["id"] ? "selected" : '';?> value="<?php echo $item["id"];?>"><?php echo $item["category_name"];?></option>
              <?php }?>
          </select>
          <select name="posts_status" class="form-control input-sm posts_status">
            <option value="all_status">所有状态</option>
            <option <?php echo isset($_GET["posts_status"]) && $_GET["posts_status"] === "drafted" ? "selected" : '';?> value="drafted">草稿</option>
            <option <?php echo isset($_GET["posts_status"]) && $_GET["posts_status"] === "published" ? "selected" : '';?> value="published">已发布</option>
            <option <?php echo isset($_GET["posts_status"]) && $_GET["posts_status"] === "trashed" ? "selected" : '';?> value="trashed">回收站</option>
          </select>
          <button class="btn btn-default btn-sm" type="submit">筛选</button>
        </form>
        <ul class="pagination pagination-sm pull-right">
          <li style="<?php echo $current_page < (($visible-1)/2 + 2) ? "display: none;" : ''; echo $total_page < 5 ? "display: none;" : '';?>"><a href="?p=<?php echo "1" . $url;?>">首页</a></li>
          <li style="<?php echo $current_page < (($visible-1)/2 + 2) ? "display: none;" : ''; echo $total_page < 5 ? "display: none;" : '';?>"><a href="?p=<?php echo $current_page - 1 . $url;?>">上一页</a></li>
          <?php for($i = (int)$begin;$i<=$end;$i++){?>
            <li style="<?php echo $end === 1 ? 'display: none;' : '';?>" class="<?php echo $i===$current_page ? 'active' : '';?>"><a href="?p=<?php echo $i . $url;?>"><?php echo $i;?></a></li>
          <?php }?>
          <li style="<?php echo ($current_page <= $total_page && $current_page >= ($total_page - ($visible-1)/2)) ? "display: none;" : ''; echo $total_page < $visible ? "display: none;" : '';?>"><a href="?p=<?php echo $current_page + 1 . $url;?>">下一页</a></li>
          <li style="<?php echo ($current_page <= $total_page && $current_page >= ($total_page - ($visible-1)/2)) ? "display: none;" : ''; echo $total_page < $visible ? "display: none;" : '';?>"><a href="?p=<?php echo $total_page . $url;?>">尾页</a></li>
        </ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>标题</th>
            <th>作者</th>
            <th>分类</th>
            <th class="text-center">发表时间</th>
            <th class="text-center">状态</th>
            <th class="text-center" width="100">操作</th>
          </tr>
        </thead>
        <tbody>
        <?php echo isset($empty_data) ? $empty_data : '';?>
        <?php foreach ($posts_data as $value){?>
            <tr>
                <td class="text-center"><input type="checkbox" data-id="<?php echo $value["id"];?>"></td>
                <td><?php echo $value["title"];?></td>
                <td><?php echo $value["nick_name"];?></td>
                <td><?php echo $value["category_name"];?></td>
                <td class="text-center"><?php echo format_created_time($value["created_time"]);?></td>
                <td class="text-center"><?php echo format_status($value["post_status"]);?></td>
                <td class="text-center">
                    <a href="javascript:;" class="btn btn-default btn-xs">编辑</a>
                    <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>
                </td>
            </tr>
        <?php }?>
        </tbody>
      </table>
    </div>
  </div>
  <?php include "./common/aside.php";?>
  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
</body>
</html>