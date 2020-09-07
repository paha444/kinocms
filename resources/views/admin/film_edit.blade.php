@include('admin.inc.head')


  <!-- Navbar -->
  @include('admin.inc.navbar')  
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  @include('admin.inc.left_menu')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Dashboard</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Dashboard v1</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">

        <form id="form_blogs_add" action="{{route('admin_film_edit_submit', $Film->id )}}" method="post" enctype="multipart/form-data">
        
        {{ csrf_field() }}


        <div class="add__file">
            <p>Изображение:</p>
            
            @if(isset($Film->image))
            <img width="300px" src="/images/films/{{ $Film->image }}"/>
            @endif            
            <input type="file" name="image"/>
        
        </div>



        <div class="content__item">
            <div class="content__title">
                <p>Название:</p>
            </div>
            <div class="sub__item">
                <div class="input__wrapper">
                    <label>
                        <input type="text" name="name" value="{{ $Film->name }}">
                        <i class="icon-pen"></i>
                    </label>
                </div>
            </div>
        </div>    

                
                
        <div class="content__item">
            <div class="content__title">
                <p>Краткое описание:</p>
            </div>
            <div class="sub__item">
                
                
                <div class="chat__form">
                        <textarea id="intro_text" name="intro_text">{{ $Film->intro_text }}</textarea>
                </div>
                
            </div>
        </div>

        <div class="content__item">
            <div class="content__title">
                <p>Полное описание:</p>
            </div>
            <div class="sub__item">
                
                
                <div class="chat__form">
                        <textarea id="full_text" name="full_text">{{ $Film->full_text }}</textarea>
                </div>
                
            </div>
        </div>
            


        <div class="content__item">
            <div class="content__title">
                <p>alias:</p>
            </div>
            <div class="sub__item">
                <div class="input__wrapper">
                    <label>
                        <input type="text" name="alias" value="{{ $Film->alias }}">
                        <i class="icon-pen"></i>
                    </label>
                </div>
            </div>
        </div>    


        <div class="content__item">
            <div class="content__title">
                <p>meta_title:</p>
            </div>
            <div class="sub__item">
                <div class="input__wrapper">
                    <label>
                        <input type="text" name="meta_title" value="{{ $Film->meta_title }}">
                        <i class="icon-pen"></i>
                    </label>
                </div>
            </div>
        </div>   
         
        <div class="content__item">
            <div class="content__title">
                <p>meta_description:</p>
            </div>
            <div class="sub__item">
                <div class="input__wrapper">
                    <label>
                        <input type="text" name="meta_description" value="{{ $Film->meta_description }}">
                        <i class="icon-pen"></i>
                    </label>
                </div>
            </div>
        </div>    



            

        <div class="content__item">    
            <div class="content__title">
                <p>Youtube code:</p>
            </div>
            <div class="sub__item" style="width: 300px;">    

                <input width="300px" type="text" placeholder="Youtube code" name="youtube" value="{{ $Film->youtube }}">
            
            </div>
        </div>
       

            <div class="content__item">
                <select name="status" required>

            @if($Film->status==1)
            <option value="1" selected="selected">Публиковать</option> 
            <option value="0">Не публиковать</option> 
            @else            
            <option value="0" selected="selected">Не публиковать</option> 
            <option value="1">Публиковать</option> 
            @endif            


                </select>
            </div>
          
                
        <div class="content__item">    
            <div class="sub__item">    
                
              <button type="submit" class="btn btn-primary btn-sm">
                  Save
              </button>
            </div>

        </div>
        
        </form>
    </section>

    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  
@include('admin.inc.footer')