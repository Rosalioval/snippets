@extends('layouts.app')

@section('head-link')
  <link href="/css/admin/user/profile.css" rel="stylesheet">
@endsection

@section('content')
  <div class="container-fluid" id="admin-user-profile" v-if="dataReady" v-cloak>
    <div class="row">
      <div class="col-sm-4">
        <div class="card">
          <div class="card-block">
            <h4 class="card-title"></h4>
            <h6></h6>
          </div>
        </div>
      </div>
      <div class="col-sm-8">
        <ul class="grid-list grid-list-4 grid-list-1-xs grid-list-2-sm user-profile-grid">
          <li>
            <article class="card">
              <div class="card-block"></div>
            </article>
          </li>
        </ul>
      </div>
    </div>
    <div class="card">
      <div class="card-block">
        <nav class="user-resources-nav">
          <small>Acciones</small>
          <a href="#" class="card-link">accion</a>
          <a href="#" class="card-link">accion</a>
          <a href="#" class="card-link">accion</a>
        </nav>
      </div>
    </div>
  </div>
@endsection

@section('footer-scripts')
  <script src="https://unpkg.com/vue"></script>
  <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

  <script>
    var app = new Vue({
      el: '#admin-user-profile',
      data: {},
      created: function(){
        this.getProfiles();
      },
      methods: {
        getProfiles: function(){
          var vm = this;
          var url = "{{ route('admin.user.users') }}";
          axios.get(url)
          .then(function(response){
            console.log(response);
          }).catch(function(error){
            console.log(error);
          });
        },
      }
    })
  </script>
@endsection
