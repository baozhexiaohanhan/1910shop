@extends("index.layouts.layout")
@section("content")

<div class="checkout pages section">
    <div class="container" style="height:160px">
        <div class="pages-head">
            <h3>{{$data['msg']}}</h3>
            <button class="btn button-default"><a href="/"><font color="aqua">前往首页</font></a></button>
			<button class="btn button-default"><a href="/cart"><font color="#adff2f">前往购物车</font></a></button>
        </div>
    </div>
</div>

@include("index.layouts.foot")
@endsection