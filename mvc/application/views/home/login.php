<div class="wrapper">
<form class="form-signin" action="#" method="post">       
  <h2 class="form-signin-heading">Please login</h2>
  <input type="text" class="form-control" name="login_name" placeholder="Login name" required="" autofocus="" value="<?=$model['login_name']?>" />
  <input type="password" class="form-control" name="password" placeholder="Password" required=""  value="<?=$model['password']?>"/>      
  <!-- <label class="checkbox">
    <input type="checkbox" value="remember-me" id="rememberMe" name="rememberMe"> Remember me
  </label> -->
  <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>  
  <p><?php isset($model['error']) and print($model['error']);?></p> 
</form>

</div>


