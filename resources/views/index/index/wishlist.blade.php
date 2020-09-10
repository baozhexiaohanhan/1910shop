@extends('index.layouts.layout')
@section('content')
    <div class="side-nav-panel-right">
        <ul id="slide-out-right" class="side-nav side-nav-panel collapsible">
            <li class="profil">
                <img src="img/profile.jpg" alt="">
                <h2>John Doe</h2>
            </li>
            <li><a href="setting.html"><i class="fa fa-cog"></i>Settings</a></li>
            <li><a href="about-us.html"><i class="fa fa-user"></i>About Us</a></li>
            <li><a href="contact.html"><i class="fa fa-envelope-o"></i>Contact Us</a></li>
            <li><a href="{{url('login/login')}}"><i class="fa fa-sign-in"></i>Login</a></li>
            <li><a href="{{url('login/reg')}}"><i class="fa fa-user-plus"></i>Register</a></li>
        </ul>
    </div>
<div class="cart section">
    <div class="container">
        <div class="pages-head">
            <h3><font color="#8a2be2">我喜欢</font></h3>
        </div>
        <div class="content">
            <div class="col s6">
                @foreach($pwa as $v)
                    <div class="content">
                        <a href=""><img src="{{$v['goods_img']}}" alt=""></a>
                        <h6><font color="#8a2be2">{{$v['goods_name']}}</font></h6>
                        <div class="price">
                            ${{$v['goods_price']}}
                        </div>

                        <a href="{{url('/cart')}}"><button class="btn button-default"><font color="#8a2be2">加入购物车</font></button></a>

                        <button class="btn button-default"><a href="/"><font color="#ff7f50">回到首页</font></a></button>

                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<!-- end cart -->
<script src="/static/index/js/jquery.min.js"></script>
<script src="/static/index/js/materialize.min.js"></script>
<script src="/static/index/js/owl.carousel.min.js"></script>
<script src="/static/index/js/fakeLoader.min.js"></script>
<script src="/static/index/js/animatedModal.min.js"></script>
<script src="/static/index/js/main.js"></script>
@include("index.layouts.foot")
@endsection