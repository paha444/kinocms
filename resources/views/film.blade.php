@include('inc.head')

   

@include('inc.header')

<main role="main">



  <div class="album py-5 bg-light">
    <div class="container">

      <div class="row">
        

        
        <div class="col-md-4">
                @if(isset($Film->image))
                <img width="100%" src="/images/films/{{ $Film->image }}"/>
                @endif
        </div>


        <div class="col-md-8">
          <div class="row">
            <h1>{{ $Film->name }}</h1>
          </div>
          <div class="row">
            {{ $Film->full_text }}
          </div>
          <div class="row">
            @if(isset($Film->youtube))
            <iframe width="100%" height="545" src="https://www.youtube.com/embed/{{ $Film->youtube }}" 
            frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen></iframe>
            @endif
            
          </div>
        </div>



             
        

      </div>
    </div>
  </div>

</main>


@include('inc.footer')


