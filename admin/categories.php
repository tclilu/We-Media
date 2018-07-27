<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
get_now_user();
// 接收参数
$action = isset($_GET['action']) ? $_GET['action'] : '';
if ("add" === $action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // 添加分类
    edit_category("add");
} else if ("update" === $action) {
    // 编辑数据
    // 接收id
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    if (!empty($id)) {
        // 显示当前id对应分类的信息
        $category_ById = show_categoryById($id);
    } else {
        exit("参数错误!");
    }
    // 表单提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 执行更新操作
        edit_category("update",$id);
        // 显示当前id对应分类更新后的信息
        $category_ById = show_categoryById($id);
    }
} else {
    // 未传入action参数 或者 有get方式传入的参数但键值不为action
    if (isset($_GET['action']) || (count($_GET) != 0 && key($_GET) !== "action")) {
        exit("参数错误!");
    }
}
// 操作完成 显示所有分类信息
$categories = show();

/**
 * 查询所有分类的方法
 * @return array 返回二维数组
 */
function show() {
    // 查询所有分类
    return fetch_all("select * from categories;");
}

/**
 * @param $id 当前分类对应id
 * @return array|null 返回当前id对应的分类信息
 */
function show_categoryById($id) {
    return fetch_one("select * from categories where id={$id};");
}

/**
 * 封装添加和编辑分类的方法
 * @param $method 要进行的操作 添加或编辑
 * @param string $id 要进行更新操作的分类对应的id
 */
function edit_category($method,$id='') {
    // 校验表单数据
    if (empty($_POST['name']) || empty($_POST['slug'])) {
        $GLOBALS['msg'] = "请填写表单相关信息";
        return;
    }
    // 接收表单数据
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    // 根据method做出响应
    if ("add" === $method) {
        // 执行添加操作
        $count = execute("insert into categories values(null,'{$slug}','{$name}',1);");
        // 根据添加操作返回结果给出不同提示信息
        // $count>0则为true,<0为false
        $GLOBALS['success'] = ($count > 0);
        $GLOBALS['msg'] = $count <= 0 ? "添加失败!" : "添加成功!";
    } else if ("update" === $method && isset($id)) {
        $count = execute("update categories set category_name='{$name}',slug='{$slug}' where id={$id};");
        // 根据添加操作返回结果给出不同提示信息
        // $count>0则为true,<0为false
        $GLOBALS['success'] = ($count > 0);
        $GLOBALS['msg'] = $count < 0 ? "保存失败！" : "保存成功！";
    }
}
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
        <?php if (isset($msg)) { ?>
            <?php if (isset($success)) { ?>
                <div class="alert alert-success">
                    <strong>成功！</strong><?php echo $msg; ?>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger">
                    <strong>错误！</strong><?php echo $msg; ?>
                </div>
            <?php }
        } ?>
        <div class="row">
            <div class="col-md-4">
                <form action="<?php echo isset($category_ById) ? $_SERVER['PHP_SELF'] . '?action=update&id=' . $category_ById['id'] : $_SERVER['PHP_SELF'] . '?action=add' ?>" method="post">
                    <h2><?php echo isset($category_ById) ? "编辑分类《" . $category_ById['category_name'] . "》" : "添加新分类目录"; ?></h2>
                    <div class="form-group">
                        <label for="name">名称</label>
                        <input id="name" class="form-control" name="name" type="text" placeholder="分类名称" autocomplete="off" value="<?php echo isset($category_ById) ? $category_ById['category_name'] : ''; ?>"/>
                    </div>
                    <div class="form-group">
                        <label for="slug">别名</label>
                        <input id="slug" class="form-control" name="slug" type="text" placeholder="slug" autocomplete="off" value="<?php echo isset($category_ById) ? $category_ById['slug'] : ''; ?>"/>
                        <p class="help-block">https://zce.me/category/<strong>slug</strong></p>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit"><?php echo isset($category_ById) ? "保存" : "添加"; ?></button>
                    </div>
                </form>
            </div>
            <div class="col-md-8">
                <div class="page-action">
                    <!-- show when multiple checked -->
                    <a id="del_all" class="btn btn-danger btn-sm" href="/admin/category_del.php" style="display: none">批量删除</a>
                </div>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="text-center" width="40"><input type="checkbox"></th>
                        <th>名称</th>
                        <th>Slug</th>
                        <th>状态</th>
                        <th class="text-center" width="100">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($categories)) { ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">~暂无数据~</td>
                        </tr><?php } ?>
                    <?php foreach ($categories as $k => $v) { ?>
                        <tr>
                            <td class="text-center"><input type="checkbox" data-id="<?php echo $v['id']; ?>"></td>
                            <td><?php echo $v['category_name']; ?></td>
                            <td><?php echo $v['slug']; ?></td>
                            <td><?php if ($v['category_status'] == 1) {
                                    echo "正常";
                                } else {
                                    echo "禁用";
                                } ?></td>
                            <td class="text-center">
                                <a href="/admin/categories.php?action=update&id=<?php echo $v['id']; ?>"
                                   class="btn btn-info btn-xs">编辑</a>
                                <a href="/admin/category_del.php?id=<?php echo $v['id']; ?>"
                                   class="btn btn-danger btn-xs">删除</a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include "./common/aside.php"; ?>
<script src="/static/assets/vendors/jquery/jquery.js"></script>
<script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
<script src="/static/assets/js/checkbox.js"></script>
<script>NProgress.done()</script>
</body>
</html>
