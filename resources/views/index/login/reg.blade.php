@extends('index.layouts.layout')
@section('content')

    <!-- register -->
	<div class="pages section">
		<div class="container">
			<div class="pages-head">
				<h3>REGISTER</h3>
			</div>
			<div class="register">
				<div class="row">
					<form class="col s12">
						<div class="input-field">
							<input type="text" class="validate" id='name' placeholder="请输入名称" required>
						</div>
                        <div class="input-field">
                            <input type="text" id='tel' placeholder="请输入手机号" class="validate" required>
                        </div>
						<div class="input-field">
							<input type="text" id='code'  placeholder="请输入验证码" style="width:200px" class="validate" required>
							<input type="button" id="aaa" value="获取验证码" class="validate" required>
                        </div>
                        <div class="input-field">
                            <input type="email" id='email' placeholder="请输入邮箱" class="validate" required>
                        </div>
						<div class="input-field">
							<input type="password" id='password' placeholder="请输入密码" class="validate" required>
						</div>
                        <div class="input-field">
                            <input type="password" id='passwords' placeholder="请输入确认密码" class="validate" required>
                        </div>
                        <input type="button" class="btn button-default" id="btn" value="REGISTER">
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- end register -->
	<script src="/static/index/js/jquery.min.js"></script>
	<script>
		var phoneReg = /^1[3|4|5|7|8]\d{9}$/; //手机号正则 
		var count = 60; //间隔函数，1秒执行
		var InterValObj1; //timer变量，控制时间
		var count1; //当前剩余秒数
		$(document).on('click','#aaa',function(){
			count1=count; 		 
			var tel=$('#tel').val();
			if(!phoneReg.test(tel)){
				alert(" 请输入有效的手机号码"); 
				return false;
			}
			// 设置button效果，开始计时
			$("#aaa").attr("disabled", "true");
			$("#aaa").val( + count1 + "秒再获取");
			InterValObj1=window.setInterval(SetRemainTime1,1000); //启动计时器，1秒执行一次
			// 向后台发送处理数据
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			}); 
			$.ajax({
				url:"/login/send",
				type:"post",
				data:{'tel':tel},
				dataType:'json',
				success:function(res){
					if(res.code == 1){
						alert(res.font);
					}else{
						alert(res.font);
					}
				}
			});
		})
		function SetRemainTime1(){
			if (count1 == 0) {                
				window.clearInterval(InterValObj1);//停止计时器
				$("#aaa").removeAttr("disabled");//启用按钮
				$("#aaa").val("重新发送");
			}else{
				count1--;
				$("#aaa").val( + count1 + "秒再获取");
			}
		}
		$(document).on('click','#btn',function(){
			var name=$("#name").val();
			var tel=$("#tel").val();
			var code=$("#code").val();
			var email=$("#email").val();
			var password=$("#password").val();
			var passwords=$("#passwords").val();
			if(tel==''){
				alert("请输入手机号!");
				return false;
			}else if(password==''){
				alert("请输入密码!");
				return false;
			}else if(code==''){
				alert("请输入验证码!");
				return false;
			}else{
				$.ajax({
					url:"/login/regdo",
					type:"post",
					data:{'name':name,'tel':tel,'code':code,'email':email,'password':password,'passwords':passwords},
					dataType:'json',
					success:function(res){
						if(res.code == 1){
							alert(res.font);
							window.location.href=res.url;
						}else{
							alert(res.font);
						}
					}
				});
			}
		})
	</script>
@endsection
