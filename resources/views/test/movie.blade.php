<script src="/static/js/jquery.js"></script>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h1>电影购买系统</h1>
    <form action="/test/movieadd">
        <table border="1">
                <input type="hidden" name="movie_id" value="{{$_GET['movie_id']}}">
                    @csrf
                @foreach($movie_count as $k=>$v)
                    @if($k % 6==0)
                        <tr></tr>
                    @endif
                <td>
                        {{$v['seat_num']}}
                    @if(!in_array($v['seat_num'],$seat_num))
                            <input type="checkbox" name="movie_count[]" id="" value="{{$v['seat_num']}}">
                        @else
                            ×
                        @endif
                </td>
                @endforeach
        </table>
        <button id="btn">购买</button>
    </form>
</body>
</html>
<script>
    $(document).ready(function () {
        //绑定点击事件
        $(document).on('click','#btn',function () {
            var movie_count=$(':checkbox');
            // console.log(movie_count);
            movie_count.each(function () {
                var _this=$(this);
                if(_this.prop('checked')==true){
                    console.log(_this.val());
                }
            });
        });
    });
</script>


