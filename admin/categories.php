<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 添加分类
    add_category();
}
/**
 * 添加分类的方法
 */
function add_category()
{
    // 校验表单数据
    if (!isset($_POST['name']) || !isset($_POST['slug'])) {
        $GLOBALS['msg'] = "请填写表单相关信息";
        return;
    }
    // 接收表单数据
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    // 执行添加操作
    $count = execute("insert into categories values(null,'{$slug}','{$name}');");
    // 根据添加操作返回结果给出不同提示信息
    // $count>0则为true,<0为false
    $GLOBALS['success'] = ($count > 0);
    $GLOBALS['msg'] = $count <= 0 ? "添加失败！" : "添加成功！";
}

// 查询所有分类
$categories = fetch_all("select * from categories;");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>Categories &laquo; Admin</title>
    <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
    <link rel="stylesheet" href="/static/assets/css/admin.css">
    <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
<script>NProgress.start()</script>

<div class="main">
    <?php include "./common/navbar.php"; ?>
    <div class="container-fluid">
        <div class="page-title">
            <h1>分类目录</h1>
        </div>
        <!-- 有错误信息时展示 -->
        <?php if (isset($msg)){ ?>
            <?php if ($success){ ?>
                <div class="alert alert-success">
                    <strong>成功！</strong><?php echo $msg;?>
                </div>
            <?php }else{ ?>
            <div class="alert alert-danger">
                <strong>错误！</strong><?php echo $msg;?>
            </div>
        <?php }} ?>
        <div class="row">
            <div class="col-md-4">
                <form action="" method="post">
                    <h2>添加新分类目录</h2>
                    <div class="form-group">
                        <label for="name">名称</label>
                        <input id="name" class="form-control" name="name" type="text" placeholder="分类名称"
                               autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="slug">别名</label>
                        <input id="slug" class="form-control" name="slug" type="text" placeholder="slug"
                               autocomplete="off">
                        <p class="help-block">https://zce.me/category/<strong>slug</strong></p>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">添加</button>
                    </div>
                </form>
            </div>
            <div class="col-md-8">
                <div class="page-action">
                    <!-- show when multiple checked -->
                    <a class="btn btn-danger btn-sm" href="javascript:;" style="display: none">批量删除</a>
                </div>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="text-center" width="40"><input type="checkbox"></th>
                        <th>名称</th>
                        <th>Slug</th>
                        <th class="text-center" width="100">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($categories)) { ?><tr><td colspan="4" style="text-align: center;">~暂无数据~</td></tr><?php } ?>
                    <?php foreach ($categories as $k => $v){ ?>
                        <tr>
                            <td class="text-center"><input type="checkbox"></td>
                            <td><?php echo $v['category_name']; ?></td>
                            <td><?php echo $v['slug']; ?></td>
                            <td class="text-center">
                                <a href="javascript:;" class="btn btn-info btn-xs">编辑</a>
                                <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>
                            </td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include "./common/aside.php"; ?>
<script src="/static/assets/vendors/jquery/jquery.js"></script>
<script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
<script>NProgress.done()</script>
</body>
</html>
