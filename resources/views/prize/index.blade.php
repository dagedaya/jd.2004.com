<script src="/static/js/jquery.js"></script>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h1>抽奖页面</h1>
    <button type="submit" id="btn">开始抽奖</button>
</body>
</html>
<script>
    //页面加载事件
    $(document).ready(function () {
        //给普通按钮添加点击事件
        $('#btn').click(function(){
            $.ajax({
                url:"/prize/add",
                dataType:'json',
                success:function (res) {
                    if(res.error==400003){
                        alert(res.msg);
                        location.href="/login";
                    }else if(res.error==300008){
                        alert(res.msg);
                    }else{
                        alert(res.data.prize);
                    }
                }
            });
        });
    });
</script>
