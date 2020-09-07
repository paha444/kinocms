@include('inc.head')

   



@include('inc.header')


<main role="main">
    
  @if(isset($message)) 
  <section class="jumbotron text-center">
    <div class="container">
      <p class="lead text-muted">{{ $message }}</p>
    </div>
  </section>
  @endif

  <div class="album py-5 bg-light">
    <div class="container">

      <div class="row">
        
        <h1>404</h1>
             
        

      </div>
    </div>
  </div>

</main>


@include('inc.footer')


