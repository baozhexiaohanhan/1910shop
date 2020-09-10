@extends("index.layouts.layout")
@section("content")

<!-- checkout -->
<div class="checkout pages section">
    <div class="container">
        <div class="pages-head">
            <h3>结算</h3>
        </div>
        <div class="checkout-content">
            <div class="row">
                <div class="col s12">
                    <ul class="collapsible" data-collapsible="accordion">
                        <li>
                            <div class="collapsible-header active"><h5>支付方式</h5></div>
                            <div class="collapsible-header active">
                                <div class="payment-mode">
                                    <p></p>
                                    <form method="post" action="/payDo?order_id={{$_GET['order_id']}}">
                                        {{csrf_field()}}
                                        <div class="input-field">
                                            <input checked type="radio" class="with-gap" id="bank-transfer" name="pay_type" value="1">
                                            <label for="bank-transfer"><span><img width="50px" src="/img/zfb.jpg"></span></label>
                                            <input  type="radio" class="with-gap" id="bank-transfer" name="pay_type" value="1">
                                            &nbsp&nbsp&nbsp&nbsp
                                            <label for="bank-transfer"><span><img width="60px" src="/img/weixin.png"></span></label>
                                        </div>

                                        <input type="submit" class="btn button-default" value="去支付">
                                    </form>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end checkout -->

@include("index.layouts.foot")
@endsection