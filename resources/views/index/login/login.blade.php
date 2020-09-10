@extends('index.layouts.layout')
@section('content')

<!-- login -->
<div class="pages section">
		<div class="container">
			<div class="pages-head">
				<h3>请登录</h3>
			</div>
			<div class="login">
				<div class="row">
					<form class="col s12" method="post" action="/login/logindo">
						<div class="input-field">
							<input type="text" class="validate" name='name' placeholder="请输入用户名" required>
						</div>
						<div class="input-field">
							<input type="password" class="validate" placeholder="请输入密码" required name="password">
						</div>
                        <input type="submit" value="loginv" class="btn button-default">
						<div class="input-field">
							<a href="/login/github"><img src="/img/github.jpg"  style="width:100px;height:100px"></a>
							&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
							<a href="/login/weixin"><img src="/img/weixin.png" style="width:100px;height:100px"></a>
							<img src="">
						</div>
                        <input type="submit" value="登录" class="btn button-default">
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- end login -->


@endsection
