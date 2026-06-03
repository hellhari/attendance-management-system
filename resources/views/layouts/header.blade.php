<!-- Top Bar Start -->
<div class="topbar">

    <!-- LOGO -->
    <div class="topbar-left">
        <a href="/" class="logo">
            <span>
                <h3 style="color: white; ">PRAGNAWARE SOLUTIONS</h3>
            </span>
            <i>
                <h1>A</h1>
            </i>
        </a>
    </div>

    <nav class="navbar-custom">
        <ul class="navbar-right d-flex list-inline float-right mb-0">
            
            {{-- SEARCH BAR (Hidden for Phase 2 MVP)
            <li class="dropdown notification-list d-none d-md-block">
                <form role="search" class="app-search" onsubmit="return false;">
                    <div class="form-group mb-0">
                        <input type="text" id="topBarSearch" class="form-control" placeholder="Search visitors..">
                        <button type="button"><i class="fa fa-search"></i></button>
                    </div>
                </form>
            </li>
            --}}

            <!-- full screen (JavaScript powered, safe to keep) -->
            <li class="dropdown notification-list d-none d-md-block">
                <a class="nav-link waves-effect" href="#" id="btn-fullscreen">
                    <i class="mdi mdi-fullscreen noti-icon"></i>
                </a>
            </li>

            {{-- NOTIFICATION BELL (Hidden for Phase 2 MVP) 
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <i class="mdi mdi-bell-outline noti-icon"></i>
                    <span class="badge badge-pill badge-danger noti-icon-badge">3</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg">
                    <h6 class="dropdown-item-text">Notifications (258)</h6>
                    <div class="slimscroll notification-item-list">
                        <!-- Dummy Items Removed for Cleanliness -->
                    </div>
                    <a href="javascript:void(0);" class="dropdown-item text-center text-primary">
                        View all <i class="fi-arrow-right"></i>
                    </a>
                </div>
            </li>
            --}}

            <!-- USER PROFILE (Cleaned up to only show Logout) -->
            <li class="dropdown notification-list">
                <div class="dropdown notification-list nav-pro-img">
                    <a class="dropdown-toggle nav-link arrow-none waves-effect nav-user" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <img src="assets/images/PG.png" alt="user" class="rounded-circle">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                        
                        {{-- DEAD PROFILE LINKS (Hidden)
                        <a class="dropdown-item" href="#"><i class="mdi mdi-account-circle m-r-5"></i> Profile</a>
                        <a class="dropdown-item d-block" href="#"><span class="badge badge-success float-right">11</span><i class="mdi mdi-settings m-r-5"></i> Settings</a>
                        <a class="dropdown-item" href="#"><i class="mdi mdi-lock-open-outline m-r-5"></i> Lock screen</a>
                        <div class="dropdown-divider"></div>
                        --}}

                        <!-- WORKING LOGOUT ROUTE -->
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-power text-danger"></i> {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </li>

        </ul>

        <ul class="list-inline menu-left mb-0">
            <li class="float-left">
                <button class="button-menu-mobile open-left waves-effect">
                    <i class="mdi mdi-menu"></i>
                </button>
            </li>
        </ul>

    </nav>
</div>
<!-- Top Bar End -->