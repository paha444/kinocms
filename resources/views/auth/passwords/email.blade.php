@include('inc.head')


<body onload="">
<div class="wrapper">


    @include('inc.header')


    <section class="content_block">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="block_template">
                        <div class="h2">{{ __('text.restore_password') }}</div>

                        
                    
                    @if(Session::get('message_send'))
                    <div class="register_message">{{ Session::get('message_send') }}</div>
                                                  {{ Session::forget('message_send') }}
                    
                    @else
                    
                        <form class="form-horizontal" method="POST" action="{{ route('password.email') }}">
                            {{ csrf_field() }}

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('text.proceed') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                    @endif

                    </div>

                </div>
            </div>
        </div>
    </section>



    @include('inc.footer')