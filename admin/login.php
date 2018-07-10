<?php
//开启session会话
session_start();
//引入配置文件
require_once '../config.php';
//如果提交方式为POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    login();
}
function login()
{
    //1.接收表单信息并校验
    //2.持久化
    //3.响应
    if (isset($email)) {
        $GLOBALS['msg'] = '请填写邮箱';
        return;
    }
    if (isset($password)) {
        $GLOBALS['msg'] = '请填写密码';
        return;
    }
    // 接收表单提交的数据
    $email = $_POST['email'];
    $password = $_POST['password'];
    //连接数据库
    $conn = @mysqli_connect(DB_HOST,DB_USER,DB_PWD,DB_NAME) or die("数据库连接失败！");
    // 使用limit 1，查询时只要找到1个，则不再继续查找，
    $query_result = mysqli_query($conn,"select * from users where email='{$email}' limit 1;");
    if (!$query_result){
        $GLOBALS['msg'] = '登录失败！请稍后再试！';
        return;
    }
    // 取出一行结果
    $user = mysqli_fetch_assoc($query_result);
    if (!$user){
        $GLOBALS['msg'] = '邮箱或密码不匹配';
        return;
    }
    if ($user['pwd'] !== md5($password)){
        $GLOBALS['msg'] = '邮箱或密码不匹配';
        return;
    }
    // 将当前登录用户对象存入session中
    $_SESSION['now_user'] = $user;
    //如果程序执行到此处表示验证通过，登录成功进行跳转
    header("Location: /admin/");
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>Sign in &laquo; Admin</title>
    <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="/static/assets/vendors/animate/animate.min.css">
    <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
    <link rel="stylesheet" href="/static/assets/css/admin.css">
    <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
<div class="login">
    <!--使用novalidate属性禁止客户端表单元素自动校验，设置autocomplete属性禁用浏览器缓存历史记录-->
    <form class="login-wrap <?php echo isset($msg)?'shake animated':'';?>" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" novalidate autocomplete="off">
        <img class="avatar" src="/static/assets/img/default.png">
        <!-- 有错误信息时展示 -->
        <?php if (isset($msg)) { ?>
            <div class="alert alert-danger">
                <strong>错误！</strong> <?php echo $msg; ?>
            </div>
        <?php } ?>
        <div class="form-group">
            <label for="email" class="sr-only">邮箱</label>
            <input value="<?php echo isset($_POST['email'])?$_POST['email']:'';?>" id="email" name="email" type="email" class="form-control" placeholder="邮箱" autofocus>
        </div>
        <div class="form-group">
            <label for="password" class="sr-only">密码</label>
            <input id="password" name="password" type="password" class="form-control" placeholder="密码">
        </div>
        <button class="btn btn-primary btn-block" href="index.php">登 录</button>
    </form>
</div>
<!--引入jQuery-->
<script src="/static/assets/vendors/jquery/jquery.js"></script>
<script>
    // 入口函数，确保页面加载完成后执行
    $(function () {
        // 匹配邮箱的正则表达式
        var emailRegular = /^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/;
        // 用户输入邮箱后，页面展示邮箱对应的头像
        $("#email").on("blur",function () {
            var v = $(this).val();
            // 如果邮箱输入框内容为空，则return
            if (!v || !emailRegular.test(v)) {
                return;
            }
            <!--进度条开始-->
            NProgress.start()
            $.get('/admin/api/avator.php',{ email:v },function (response) {
                if (!response) { return; }
                // 淡出完成后，在回调函数中，图片加载完成后淡入
                $(".avatar").fadeOut(function () {
                    $(this).on("load",function () {
                        // 图片完全加载成功后
                        $(this).fadeIn();
                    }).attr("src",response);
                });
                <!--进度条结束-->
                NProgress.done();
            });
        });
    });
</script>
</body>
</html>
