@include('inc.head')


<body onload="">
		<div class="wrapper">
   

@include('inc.header')
	

 <section class="content_block">
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="block_template">
					<div class="h2">Авторизация на сайте</div>



            
            <div class="login_message">{{ Session::get('message') }}</div>
            
            <form class="form-horizontal login" method="POST" action="{{ route('login') }}">
                {{ csrf_field() }}

               <p class="top">Вход</p>
               <div class="inputs-wrap">
                   
                   <label class="{{ $errors->has('email') ? ' has-error' : '' }}">
                      

                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

                        @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif


                   </label>
                   <label class="{{ $errors->has('password') ? ' has-error' : '' }}">

                        <input id="password" type="password" class="form-control" name="password" required>

                        @if ($errors->has('password'))
                            <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif


                   </label>
               </div>
              <div class="checkbox1">
                  
                  <input type="checkbox" id="checkbox_1" name="remember" {{ old('remember') ? 'checked' : '' }}> 

                  <label for="checkbox_1">Запомнить меня</label>
              </div>
               <div class="btn--enter">

                    <button type="submit" class="btn btn-primary">Вход</button>

               </div>
               <p class="bottom"><a class="register" href="/register">Регистрируйтесь</a></p>

               
               
               </form>
               
				</div>
    
			</div>
		</div>
	</div>
</section>


	
@include('inc.footer')