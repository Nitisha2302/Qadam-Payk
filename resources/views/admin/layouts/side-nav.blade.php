<aside class="sidebar-nav">
    <div class="d-flex align-items-center justify-content-center">
        <a class="navbar-brand"
            href="{{ Auth::user()->role == 1 ? route('dashboard.admin.all-cities') : (Auth::user()->role == 2 ? route('dashboard.employee.all-cities') : '#') }}">
            <img class="full-imgbox" src="{{ asset('assets/admin/images/qadampayk-dash.png') }}" width="200" alt="logo">
        </a>
    </div>
 
    <ul class="side-menu">

        <!-- Menu for role 1 -->
        @if (Auth::user()->role == 1)
            <li class="@if (Route::currentRouteName() == 'dashboard.admin.dashboard') active @endif">
                <a href="{{ route('dashboard.admin.dashboard') }}">
                    <span class="d-flex gap-3 align-items-end">
                        <i class="fas fa-tachometer-alt icon-font-size"></i>
                        <span class="nav-content-menu">Dashboard</span>
                    </span>
                </a>
            </li>
    
            <li class="@if (Route::currentRouteName() == 'dashboard.admin.all-drivers') active @endif">
                <a href="{{ route('dashboard.admin.all-drivers') }}">
                    <span class="d-flex gap-3 align-items-end">
                        <i class="fa fa-users icon-font-size"></i>
                        <span class="nav-content-menu">Drivers</span>
                    </span>
                </a>
            </li>

            <li class="@if (Route::currentRouteName() == 'dashboard.admin.all-passengers') active @endif">
                <a href="{{ route('dashboard.admin.all-passengers') }}">
                    <span class="d-flex gap-3 align-items-end">
                        <i class="fa fa-users icon-font-size"></i>
                        <span class="nav-content-menu">Passengers</span>
                    </span>
                </a>
            </li>

            <li class="@if (Route::currentRouteName() == 'dashboard.admin.all-cities') active @endif">
                <a href="{{ route('dashboard.admin.all-cities') }}">
                    <span class="d-flex gap-3 align-items-end">
                       <i class="fas fa-city icon-font-size"></i>
                        <span class="nav-content-menu">Cities</span>
                    </span>
                </a>
            </li>

            <li class="@if (Route::currentRouteName() == 'dashboard.admin.all-cars') active @endif">
                <a href="{{ route('dashboard.admin.all-cars') }}">
                    <span class="d-flex gap-3 align-items-end">
                        <i class="fas fa-car icon-font-size"></i>
                        <span class="nav-content-menu">Car Model</span>
                    </span>
                </a>
            </li>

            
            <li class="@if (Route::currentRouteName() == 'dashboard.admin.all-services') active @endif">
                <a href="{{ route('dashboard.admin.all-services') }}">
                    <span class="d-flex gap-3 align-items-end">
                        <i class="fas fa-car icon-font-size"></i>
                        <span class="nav-content-menu">Services</span>
                    </span>
                </a>
            </li>

            <!-- <li class="@if (Route::currentRouteName() == 'dashboard.admin.bookings.index') active @endif">
                <a href="{{ route('dashboard.admin.bookings.index') }}">
                    <span class="d-flex gap-3 align-items-end">
                        <i class="fas fa-car icon-font-size"></i>
                        <span class="nav-content-menu">Bookings management</span>
                    </span>
                </a>
            </li> -->

            <li class="@if (Route::currentRouteName() == 'dashboard.admin.all-query') active @endif">
                <a href="{{ route('dashboard.admin.all-query') }}">
                    <span class="d-flex gap-3 align-items-end">
                       <i class="fas fa-envelope icon-font-size"></i>
                        <span class="nav-content-menu">Queries</span>
                    </span>
                </a>
            </li>

            <li class="@if (Route::currentRouteName() == 'dashboard.admin.privacy-policy.edit') active @endif">
                <a href="{{ route('dashboard.admin.privacy-policy.edit') }}">
                    <span class="d-flex gap-3 align-items-end">
                      <i class="fas fa-lock icon-font-size"></i>
                        <span class="nav-content-menu">Privacy Policy</span>
                    </span>
                </a>
            </li>

            <li class="@if (Route::currentRouteName() == 'dashboard.admin.terms-comditions-edit') active @endif">
                <a href="{{ route('dashboard.admin.terms-comditions-edit') }}">
                    <span class="d-flex gap-3 align-items-end">
                       <i class="fas fa-file-alt icon-font-size"></i>
                        <span class="nav-content-menu">Terms & Conditions</span>
                    </span>
                </a>
            </li>

        @endif
    </ul>
</aside>

