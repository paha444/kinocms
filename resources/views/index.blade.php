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
        

@if(isset($films))


@foreach ($films as $film)

        
        <div class="col-md-4">
          <div class="card mb-4 shadow-sm">
                @if(isset($film->image))
                <img width="100%" src="/images/films/{{ $film->image }}"/>
                @endif
            <div class="card-body">
              <p class="card-text">{{ $film->name }}</p>
              <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group">
                  <a href="{{ route('film',$film->id) }}"><button type="button" class="btn btn-sm btn-outline-secondary">Открыть</button></a>
                </div>
                <small class="text-muted">9 mins</small>
              </div>
            </div>
          </div>
        </div>



@endforeach             

@endif
             
        

      </div>
    </div>
  </div>

</main>


@include('inc.footer')


