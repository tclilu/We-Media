<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
get_now_user();
// 接收参数
$action = isset($_GET['action']) ? $_GET['action'] : '';
if ("add" === $action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // 进行新用户添加操作
    edit_user("add");
} else if ("update" === $action) {
    // 接收id
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    // 校验参数
    if (!empty($id)) {
        $user_ById = show_userById($id);
    } else {
        exit("参数错误!");
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 执行更新操作
        edit_user("update",$id);
        // 显示当前id对应用户更新完成后的信息
        $user_ById = show_userById($id);
    }
} else {
    // 未传入action参数 或者 有get方式传入的参数但键值不为action
    if (isset($_GET['action']) || (count($_GET) != 0 && key($_GET) !== "action")) {
        exit("参数错误!");
    }
}
// 查询所有用户信息 显示在页面上
$all_users = show_all_users();

/**
 * 显示当前要编辑的用户的信息的方法
 * @param $id 要查询的用户的id
 * @return array|null 返回当前user的信息
 */
function show_userById($id) {
    return fetch_one("select id,email,slug,nick_name,pwd from users where id={$id};");
}

/**
 * 封装添加和更新用户的方法
 * @param $method 要进行的操作 添加或编辑
 * @param string $id 更新操作时必要的参数:当前要更新用户的id
 */
function edit_user($method,$id='') {
    // 校验表单数据
    if (empty($_POST['email']) || empty($_POST['slug']) || empty($_POST['nickname']) || empty($_POST['password'])) {
        $GLOBALS['msg'] = "请填写表单相关信息!";
        return;
    }
    // 接收表单数据
    $email = $_POST['email'];
    $slug = $_POST['slug'];
    $nick_name = $_POST['nickname'];
    $pwd = $_POST['password'];
    // 进行数据处理
    // 正则校验邮箱格式
    $match_num = preg_match('/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/', $email);
    if ($match_num <= 0) {
        $GLOBALS['msg'] = "邮箱格式不正确";
        return;
    }
    // 对密码进行md5加密
    $pwd = md5($pwd);
    // 根据参数做对应响应
    if ("add" === $method) {
        $count = execute("insert into users(slug,email,pwd,nick_name) values('{$slug}','{$email}','{$pwd}','{$nick_name}');");
        $GLOBALS['success'] = $count > 0;
        $GLOBALS['msg'] = ($count > 0) ? "添加成功!" : "添加失败!";
    } else if ("update" === $method && isset($id)) {
        $count = execute("update users set slug='{$slug}',email='{$email}',pwd='{$pwd}',nick_name='{$nick_name}' where id={$id};");
        $GLOBALS['success'] = $count > 0;
        $GLOBALS['msg'] = ($count > 0) ? "保存成功!" : "更新失败!";
    }
}

/**
 * 查询所有用户信息的方法
 * @return array 以二维数组形式返回所有用户信息
 */
function show_all_users() {
    return fetch_all("select * from users;");
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>Users &laquo; Admin</title>
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
            <h1>用户</h1>
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
        <div class="alert alert-danger email-exist" style="display: none;">
            <strong>该邮箱已被注册</strong>
        </div>
        <div class="row">
            <div class="col-md-4">
                <form action="<?php echo isset($user_ById) ? $_SERVER['PHP_SELF'] . '?action=update&id=' . $user_ById['id'] : $_SERVER['PHP_SELF'] . '?action=add'; ?>" method="post" novalidate autocomplete="off">
                    <h2><?php echo isset($user_ById) ? '编辑用户:' . $user_ById['nick_name'] : '添加新用户';?></h2>
                    <div class="form-group">
                        <label for="email">邮箱</label>
                        <input id="email" class="form-control" name="email" type="email" placeholder="邮箱" value="<?php echo isset($user_ById) ? $user_ById['email'] : ''; ?>" <?php echo isset($user_ById) ? 'readonly' : '';?>>
                    </div>
                    <div class="form-group">
                        <label for="slug">别名</label>
                        <input id="slug" class="form-control" name="slug" type="text" placeholder="slug" value="<?php echo isset($user_ById) ? $user_ById['slug'] : ''; ?>">
                        <p class="help-block">https://zce.me/author/<strong>slug</strong></p>
                    </div>
                    <div class="form-group">
                        <label for="nickname">昵称</label>
                        <input id="nickname" class="form-control" name="nickname" type="text" placeholder="昵称" value="<?php echo isset($user_ById) ? $user_ById['nick_name'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">密码</label>
                        <input id="password" class="form-control" name="password" type="password" placeholder="密码" value="<?php echo isset($user_ById) ? $user_ById['pwd'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit"><?php echo isset($user_ById) ? '保存' : '添加'; ?></button>
                    </div>
                </form>
            </div>
            <div class="col-md-8">
                <div class="page-action">
                    <!-- show when multiple checked -->
                    <a id="del_all" class="btn btn-danger btn-sm" href="/admin/user_del.php" style="display: none">批量删除</a>
                </div>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="text-center" width="40"><input type="checkbox"></th>
                        <th class="text-center" width="80">头像</th>
                        <th>邮箱</th>
                        <th>别名</th>
                        <th>昵称</th>
                        <th>状态</th>
                        <th class="text-center" width="100">操作</th>
                    </tr>
                    </thead>
                    <tbody> <?php if (empty($all_users)) { ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">~暂无用户信息~</td>
                        </tr> <?php } else { ?>
                        <?php foreach ($all_users as $k => $v) { ?>
                            <tr>
                                <td class="text-center"><input type="checkbox" data-id="<?php echo $v['id'] ?>"></td>
                                <td class="text-center"><img class="avatar" src="<?php echo $v['avator']; ?>"></td>
                                <td><?php echo $v['email']; ?></td>
                                <td><?php echo $v['slug']; ?></td>
                                <td><?php echo $v['nick_name']; ?></td>
                                <td><?php if ($v['user_status'] === 'unactivated') {
                                        echo "未激活";
                                    } elseif ($v['user_status'] === 'activated') {
                                        echo "已激活";
                                    } elseif ($v['user_status'] === 'forbidden') {
                                        echo "禁用";
                                    } elseif ($v['user_status'] === 'trashed') {
                                        echo "回收站";
                                    } ?></td>
                                <td class="text-center">
                                    <a href="/admin/users.php?action=update&id=<?php echo $v['id']; ?>" class="btn btn-default btn-xs">编辑</a>
                                    <a href="/admin/user_del.php?id=<?php echo $v['id']; ?>" class="btn btn-danger btn-xs">禁用</a>
                                </td>
                            </tr>
                        <?php }
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include "./common/aside.php"; ?>
<script src="/static/assets/vendors/jquery/jquery.js"></script>
<script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
<script>
    $(function () {
        // 当邮箱输入框失去焦点,对输入的邮箱数据进行校验,
        // 1.常规正则校验
        // 匹配邮箱的正则表达式
        var emailRegular = /^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/;
        // 避免重复获取jQ对象
        var jQ_email = $("#email");
        // 保存输入框值的变量
        var v = jQ_email.val();
        // email输入框鼠标失去焦点事件
        jQ_email.on("blur", function () {
            // 获取输入框中输入的值
            var now_v = jQ_email.val();
            // 如果输入框中的值没有改变,则return
            if (now_v === v) {
                return;
            } else {
                v = now_v;
            }
            // 如果输入的值为空,或者不匹配正则表达式,则return
            if (!now_v || !emailRegular.test(now_v)) {
                return;
            }
            <!--进度条开始-->
            NProgress.start();
            $.get('/admin/api/user.php', {email: now_v, field: "email"}, function (response) {
                <!--进度条结束-->
                NProgress.done();
                if (!response) {
                    return;
                }
                // 相等表示输入的邮箱用户已存在
                if (response === now_v) {
                    $(".email-exist").stop().fadeIn(function () {
                        $(this).stop().fadeOut(5000);
                    });
                }
            });
        });
    });
</script>
<script src="/static/assets/js/checkbox.js"></script>
<script>NProgress.done();</script>
</body>
</html>
