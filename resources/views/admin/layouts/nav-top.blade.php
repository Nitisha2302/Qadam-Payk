<div class="navbar-box main-space-box">
      <!-- <div class="container-fluid"> -->
    <div class="d-flex justify-content-end">
      <div class="container-fluid gx-0">
        <div class="col-12">
          <nav class="navbar navbar-expand-sm">
              
              <!-- <div class="collapse navbar-collapse" id="navbarSupportedContent"> -->
                <form class="me-auto mb-0 mb-lg-0 d-flex gap-3 align-items-center" role="search" id="searchForm" method="GET" action="{{ route('global-search') }}">
                    <div class="slide-nav" id="nav_slidebar">
                      <span></span> 
                    </div>
                  <!-- <div class="d-flex search-box">
                      <input class="form-control me-2" type="search" placeholder="search here" aria-label="Search" name="search" id="project_name" value="{{ request('search') }}">
                      
                      <button type="submit"><i class="fas fa-search"></i></button>
                  </div> -->
                </form>
                
                <div class="d-flex align-items-center gap-4">
                  <a href="#" class="btn-box text-decoration-none text-uppercase position-relative">
                      <i class="fas fa-user"></i>
                      @if(auth()->check())
                        @if(auth()->user()->role == 1)
                            <!-- Display "ADMIN" if role is 1 -->
                            <p class="m-0">ADMIN</p>
                        @elseif(auth()->user()->role == 2)
                            <!-- Display first and last name if role is 2 -->
                            <p class="m-0">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                        @endif
                      @endif
                      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                          @csrf
                      </form>
                      <div class="logout-option">
                          <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</button>
                      </div>
                  </a>
                </div>

              <!-- </div> -->
            </div>
          </nav>
        </div>
    </div>
  </div>
  

