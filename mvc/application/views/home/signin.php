<div class="wrapper">
	<div class="row">
        <div class="col-xs-12 col-sm-12 col-md-4 col-md-push-4 well well-sm">
            <legend><a href="http://www.jquery2dotnet.com"><i class="glyphicon glyphicon-globe"></i></a> Sign up!</legend>
            <form action="#" method="post" class="form" role="form">
            <div class="row">
                <div class="col-xs-6 col-md-6">
                    <input class="form-control" name="firstname" placeholder="First Name" type="text"
                        required autofocus value="<?=@$model['firstname'] ?>"/>
                </div>
                <div class="col-xs-6 col-md-6">
                    <input class="form-control" name="lastname" placeholder="Last Name" type="text" required value="<?=@$model['lastname']?>"/>
                </div>
            </div>
            <input class="form-control" name="login_name" placeholder="Login name" type="text" value="<?=@$model['login_name']?>"/>
            <input class="form-control" name="email" placeholder="Your Email" type="email" value="<?=@$model['email']?>"/>
            <input class="form-control" name="email2" placeholder="Re-enter Email" type="email" value="<?=@$model['email2']?>"/>            
            <input class="form-control" name="telephone" placeholder="Telephone" type="tel" required value="<?=@$model['telephone']?>"/>
            <input class="form-control" name="cellphone" placeholder="Cellphone" type="tel" required value="<?=@$model['cellphone']?>"/>
            <input class="form-control" name="password" placeholder="Password" type="password" required value="<?=@$model['password'] ?>"/>
            <input class="form-control" name="password2" placeholder="Confirm password" type="password" required value="<?=@$model['password2']?>"/>
            
            <br />
            <br />
            <button class="btn btn-lg btn-primary btn-block" type="submit">
                Sign up</button>
            </form>
            <p><?=@$model['error']?></p>
        </div>
    </div>
</div>