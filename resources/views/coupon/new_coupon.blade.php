@extends('layout.layout')
@section('content')
    <h1 style="text-align: center">新人专有优惠劵</h1>
    <form style="margin-left: 700px;line-height: 30px;" method="post" id="coupon-discount" action="/add">
        @foreach($type as $k=>$v)
            @if($v['coupon_type']=="满减券")
                {{$v['coupon_type']}}
                满{{$v['full']}}减{{$v['few']}}
                <input type="radio" name="fullfew" id="" value="{{$v['coupon_id']}}"><br>
                @else
                {{$v['coupon_type']}}
                满{{$v['full']}}打{{$v['few']}}折
                <input type="radio" name="fullfew" id="" value="{{$v['coupon_id']}}"><br>
            @endif
        @endforeach
            <input type="submit" class="sui-btn btn-xlarge btn-danger" value="领取优惠劵">
    </form>
@endsection
<script src="/static/js/jquery.js"></script>
<script>
    $(document).ready(function () {
        $(document).on('submit','#coupon-discount',function () {
            var radio=$(':radio');
            _count=false;
            radio.each(function(){
                var _this=$(this);
                if(_this.prop('checked')==true){
                    _count=true;
                }
            });
            if(_count==false){
                alert('请选择优惠券');
                return _count;
            }else{
                return _count;
            }
        })
    });
;</script>
