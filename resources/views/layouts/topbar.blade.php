@php
    $isEmployer = auth('employer')->check();
    $currentUser = $isEmployer ? auth('employer')->user() : auth()->user();
    $userName = $isEmployer ? ($currentUser->nom ?? 'Employé') : ($currentUser->name ?? 'Utilisateur');
    $logoutRoute = $isEmployer ? route('employer_space.logout') : route('logout');
@endphp

<style>
    .app-header {
        background: #ffffff !important;
        border-bottom: 1px solid hsl(186, 67%, 88%) !important;
        box-shadow: 0 2px 8px rgba(0, 174, 199, 0.08) !important;
    }

    .app-header-inner {
        background: #ffffff !important;
    }

    #sidepanel-toggler {
        color: hsl(189, 100%, 23%) !important;
        transition: color 0.2s ease;
    }

    #sidepanel-toggler:hover {
        color: hsl(194, 100%, 46%) !important;
    }

    /* Avatar dropdown */
    .app-user-dropdown .dropdown-toggle img {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 2px solid hsl(194, 100%, 46%);
        transition: all 0.2s ease;
    }

    .app-user-dropdown .dropdown-toggle img:hover {
        border-color: hsl(189, 100%, 23%);
        box-shadow: 0 4px 12px rgba(0, 174, 199, 0.3);
    }

    .app-user-dropdown .dropdown-toggle::after {
        display: none;
    }

    /* Dropdown menu */
    .app-user-dropdown .dropdown-menu {
        border: 1px solid hsl(186, 67%, 88%) !important;
        border-radius: 10px !important;
        box-shadow: 0 8px 24px rgba(0, 174, 199, 0.15) !important;
        padding: 8px 0 !important;
        min-width: 180px;
    }

    .app-user-dropdown .dropdown-item {
        color: hsl(189, 100%, 23%) !important;
        font-weight: 500;
        padding: 10px 18px;
        transition: all 0.2s ease;
        font-size: 0.9rem;
    }

    .app-user-dropdown .dropdown-item:first-child {
        font-weight: 700;
        color: hsl(189, 100%, 23%) !important;
        pointer-events: none;
    }

    .app-user-dropdown .dropdown-item:hover {
        background: hsl(186, 67%, 94%) !important;
        color: hsl(194, 100%, 46%) !important;
    }

    .app-user-dropdown .dropdown-divider {
        border-color: hsl(186, 67%, 88%) !important;
        margin: 4px 0;
    }

    /* Logout item */
    .app-user-dropdown .dropdown-item.logout-item {
        color: #dc2626 !important;
    }

    .app-user-dropdown .dropdown-item.logout-item:hover {
        background: #fef2f2 !important;
        color: #b91c1c !important;
    }
</style>

<div class="app-header-inner">
    <div class="container-fluid py-2">
        <div class="app-header-content">
            <div class="row justify-content-between align-items-center">

                <div class="col-auto">
                    <a id="sidepanel-toggler" class="sidepanel-toggler d-inline-block d-xl-none" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" role="img">
                            <title>Menu</title>
                            <path stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2" d="M4 7h22M4 15h22M4 23h22"></path>
                        </svg>
                    </a>
                </div>

                <div class="app-utilities col-auto">
                    <div class="app-utility-item app-user-dropdown dropdown">
                        <a class="dropdown-toggle" id="user-dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name={{ $userName }}&background=00c2e0&color=ffffff&bold=true" 
                                 alt="user profile">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="user-dropdown-toggle">
                            <li>
                                <a class="dropdown-item" href="#">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" class="me-2" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                    </svg>
                                    {{ $userName }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ $logoutRoute }}" id="logout-form">
                                    @csrf
                                </form>
                                <a class="dropdown-item logout-item" href="#"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" class="me-2" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                                        <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                                    </svg>
                                    Log Out
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>