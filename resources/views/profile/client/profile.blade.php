@include('inc.head')
<body onload="load();">
   <div class="wrapper">
   @include('inc.header')
   <section class="breadcrumbs">
      <div class="container">
         <ul class="breadcrumb">
            <li><a href="{{ route('index') }}">{{ __('text.main_page') }}</a></li> 
            <li class="active">{{ __('text.profile') }}</li>
         </ul>
      </div>
   </section>
   <section class="content_block">
      <div class="container" id="profile">
         <div class="row">
            <div class="col-sm-3">
               <div class="user_block block_template">
                  <?php if(!empty(Auth::user()->avatar)){ 
                     $avatar = '/images/avatars/'.Auth::user()->avatar;
                     }else{
                     $avatar = '/img/icons/empty.png';
                     }   
                     ?>                        
                  <div class="user_img">
                     <img src="<?php echo $avatar; ?>">
                  </div>
                  <div class="user_info">
                     <div class="badge">
                        <span>
                        <?php echo __('text.'.Auth::user()->role_name) ?>
                        </span>
                     </div>
                     <div class="user_name">
                       <?php echo Auth::user()->family ?>
                       <?php echo Auth::user()->fullname ?>
                       <?php echo Auth::user()->surname ?>
                     </div>
                     <div class="other">
                        <div class="list">
                           <b>
                           {{ __('text.rating') }}
                           </b>
                           <span class="glyphicon glyphicon-star"></span>
                           <span class="glyphicon glyphicon-star"></span>
                           <span class="glyphicon glyphicon-star"></span>
                           <span class="glyphicon glyphicon-star"></span>
                           <span class="glyphicon glyphicon-star"></span>
                        </div>
                        <div class="list">
                           <b>
                           {{ __('text.age') }}
                           </b>
<?php 
                  
$date1 = Auth::user()->date;
if(isset($date1)){
$date2 = date('d.m.Y');

$diff = abs(strtotime($date2) - strtotime($date1));

$years = floor($diff / (365*60*60*24));

echo $years.' '.__('text.years');

}
                  
?>
                        </div>
                       <div class="list">
                          <b>
                          {{ __('text.experience') }}
                          </b>
                          <?php echo $user->work_experience ?> {{ __('text.years') }}
                       </div>
                        <div class="list">
                           <b>
                           {{ __('text.city') }}
                           </b>
                           <?php echo $user->city ?>
                        </div>
                        <div class="list">
                           <b>
                           {{ __('text.phone') }}
                           </b>
                           <?php echo $user->phone ?>
                        </div>
                        <div class="list">
                           <b>
                           {{ __('text.email') }}
                           </b>
                           <?php echo $user->email ?>
                        </div>
                        <div class="list">
                           <b>
                           {{ __('text.telegram') }}
                           </b>
                           <a href=""><?php echo $user->telegram ?></a>
                        </div>
                     </div>
                     <button style="width:100%;" class="btn_default" 
                        onclick="document.location.href='{{ route('profile_edit_profile_client') }}'">
                     <i class="far fa-save"></i>
                     {{ __('text.edit') }}
                     </button>
                  </div>
               </div>
            </div>
            <div class="col-sm-9">

               <div class="wblock_template">
                  <div class="panel panel-default">
                     <div class="tab-content">
                        <button class="pull-left btn btn-default" onclick="window.location.href = '{{ route('request_help') }}';">
                        {{ __('text.order_a_consultation') }}
                        </button>
                        <div class=clrln></div>
                     </div>
                  </div>
               </div>
               <div class="wblock_template">
                  <div class="panel panel-default">
                     <div class="panel-heading">
                        {{ __('text.my_affairs') }} 
                        <div class="btn-group btn-group-xs pull-right">
                           <button type="button" class="btn btn-primary {{ request()->is('*/everything') ? 'active' : '' }}{{ request()->is('*/client') ? 'active' : '' }}"
                           onclick="window.location.href = '{{ route('profile_index_affairs_client','everything') }}';">
                           {{ __('text.everything') }}
                           </button>
                           <button type="button" class="btn btn-warning {{ request()->is('*/in_work') ? 'active' : '' }}"
                           onclick="window.location.href = '{{ route('profile_index_affairs_client','in_work') }}';">
                           {{ __('text.in_work') }}
                           </button>
                           <button type="button" class="btn btn-success {{ request()->is('*/completed') ? 'active' : '' }}"
                           onclick="window.location.href = '{{ route('profile_index_affairs_client','completed') }}';">
                           {{ __('text.completed_2') }}
                           </button>
                        </div>
                     </div>
                     <!-- Table -->
                     <table class="table">
                        <thead>
                           <tr>
                              <th>
                                 #
                              </th>
                              <th>
                                 {{ __('text.question_document') }}
                              </th>
                              <th>
                                 {{ __('text.deadlines') }}
                              </th>
                              <th>
                                 {{ __('text.status') }}
                              </th>
                              <th>
                                 {{ __('text.cost') }}
                              </th>
                           </tr>
                        </thead>
                        <tbody>
                            @foreach($offers as $offer)                                         
                           <tr>
                              <td>
                                 {{ $offer->id }}
                              </td>
                              <td>
                                 <a href="{{ route('request_client', $offer->id ) }}">
                                 {{ $offer->RequestCaption }}
                                 @if($offer->messages_count)
                                 <span style="color: red;">({{ $offer->messages_count }})</span>
                                 @endif                                                   
                                 </a>
                                 <?php /*
                                 <a href="{{ route('request_edit_client', $offer->id ) }}">Редактировать</a>
                                 */ ?>
                              </td>
                              <td>
                                 2019-11-26 06:17:56.209185
                              </td>
                              <td>

<?php                         
//                                echo __('text.'.$offer->status);

        if(isset($offer->status)){
            echo __('text.'.$offer->status.'_bt'); //$status[$offer->status];
        }else{
            echo __('text.'.$offer->offer_status.'_bt'); //$status[$offer->offer_status];
        }


?>  
                              </td>
                              <td>
                                 <b>
                                 12 900 руб
                                 </b>
                              </td>
                           </tr>
                           @endforeach                                      
                        </tbody>
                     </table>
                  </div>
               </div>
              
               
            </div>
         </div>
      </div>
   </section>
   @include('inc.footer')