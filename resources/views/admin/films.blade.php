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

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Фильмы</h3>

          <a class="btn btn-info btn-sm" href="{{ route('admin_film_add') }}">
              <i class="fas fa-pencil-alt">
              </i>
              Add
          </a>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
              <i class="fas fa-minus"></i></button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="Remove">
              <i class="fas fa-times"></i></button>
          </div>
          
          
          
        </div>
        <div class="card-body p-0">
          <table class="table table-striped projects">
              <thead>
                  <tr>
                      <th style="width: 1%">
                          #
                      </th>
                      <th style="width: 20%">
                          Project Name
                      </th>
                      <th style="width: 30%">
                          Team Members
                      </th>
                      <th>
                          Project Progress
                      </th>
                      <th style="width: 8%" class="text-center">
                          Status
                      </th>
                      <th style="width: 20%">
                      </th>
                  </tr>
              </thead>
              <tbody>


<?php if(isset($films)){ ?>



<?php foreach($films as $key=>$film){ ?>

                  <tr>
                      <td>
                          <?php echo $film->id ?>
                      </td>
                      <td>
                          <a>
                              <?php echo $film->name ?>
                          </a>
                          <br>
                          <small>
                              Created 01.01.2019
                          </small>
                      </td>
                      <td>
                          <ul class="list-inline">
                              <li class="list-inline-item">
                                  <?php if(isset($film->image)): ?>
                                  <img alt="Avatar" class="table-avatar" src="/images/films/<?php echo $film->image ?>">
                                  <?php endif; ?>
                              </li>
                          </ul>
                      </td>
                      <td class="project_progress">
                          <div class="progress progress-sm">
                              <div class="progress-bar bg-green" role="progressbar" aria-volumenow="57" aria-volumemin="0" aria-volumemax="100" style="width: 57%">
                              </div>
                          </div>
                          <small>
                              57% Complete
                          </small>
                      </td>
                      <td class="project-state">
                          <?php if($film->status == 1): ?>  
                          <span class="badge badge-success">Public</span>
                          <?php else: ?>  
                          <span class="badge badge-warning">No public</span>
                          <?php endif; ?> 
                      </td>
                      <td class="project-actions text-right">
                          <a class="btn btn-primary btn-sm" href="#">
                              <i class="fas fa-folder">
                              </i>
                              View
                          </a>
                          <a class="btn btn-info btn-sm" href="{{ route('admin_film_edit', $film->id ) }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                          </a>
                          <a class="btn btn-danger btn-sm" href="{{ route('admin_film_delete', $film->id ) }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                          </a>
                      </td>
                  </tr>




    <?php } ?>
             

<?php } ?>
             
             
              



                  
              </tbody>

<tr>
    <td>
        <?php echo $films->render(); ?> 
    </td>
</tr>

          </table>


        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->

    </section>

    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  
@include('admin.inc.footer')