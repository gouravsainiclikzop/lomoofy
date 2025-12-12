<!-- sa-app__toolbar -->
<div class="sa-toolbar sa-toolbar--search-hidden sa-app__toolbar">
    <div class="sa-toolbar__body">
        <div class="sa-toolbar__item">
            <button class="sa-toolbar__button" type="button" aria-label="Menu" data-sa-toggle-sidebar="">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M1,11V9h18v2H1z M1,3h18v2H1V3z M15,17H1v-2h14V17z"></path>
                </svg>
            </button>
        </div>
        
        <div class="sa-toolbar__item sa-toolbar__item--search">
            <form class="sa-search sa-search--state--pending">
                <div class="sa-search__body">
                    <label class="visually-hidden" for="input-search">Search for:</label>
                    <div class="sa-search__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M16.243 14.828C16.243 14.828 16.047 15.308 15.701 15.654C15.34 16.015 14.828 16.242 14.828 16.242L10.321 11.736C9.247 12.522 7.933 13 6.5 13C2.91 13 0 10.09 0 6.5C0 2.91 2.91 0 6.5 0C10.09 0 13 2.91 13 6.5C13 7.933 12.522 9.247 11.736 10.321L16.243 14.828ZM6.5 2C4.015 2 2 4.015 2 6.5C2 8.985 4.015 11 6.5 11C8.985 11 11 8.985 11 6.5C11 4.015 8.985 2 6.5 2Z"></path>
                        </svg>
                    </div>
                    <input type="text" id="input-search" class="sa-search__input" placeholder="Search..." autocomplete="off"/>
                    <button class="sa-search__cancel d-sm-none" type="button" aria-label="Close search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                            <path d="M10.8,10.8L10.8,10.8c-0.4,0.4-1,0.4-1.4,0L6,7.4l-3.4,3.4c-0.4,0.4-1,0.4-1.4,0l0,0c-0.4-0.4-0.4-1,0-1.4L4.6,6L1.2,2.6 c-0.4-0.4-0.4-1,0-1.4l0,0c0.4-0.4,1-0.4,1.4,0L6,4.6l3.4-3.4c0.4-0.4,1-0.4,1.4,0l0,0c0.4,0.4,0.4,1,0,1.4L7.4,6l3.4,3.4 C11.2,9.8,11.2,10.4,10.8,10.8z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mx-auto"></div>
        
        <div class="sa-toolbar__item d-sm-none">
            <button class="sa-toolbar__button" type="button" aria-label="Show search" data-sa-action="show-search">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M16.243 14.828C16.243 14.828 16.047 15.308 15.701 15.654C15.34 16.015 14.828 16.242 14.828 16.242L10.321 11.736C9.247 12.522 7.933 13 6.5 13C2.91 13 0 10.09 0 6.5C0 2.91 2.91 0 6.5 0C10.09 0 13 2.91 13 6.5C13 7.933 12.522 9.247 11.736 10.321L16.243 14.828ZM6.5 2C4.015 2 2 4.015 2 6.5C2 8.985 4.015 11 6.5 11C8.985 11 11 8.985 11 6.5C11 4.015 8.985 2 6.5 2Z"></path>
                </svg>
            </button>
        </div>
        
        <div class="dropdown sa-toolbar__item">
            <button class="sa-toolbar-user" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" data-bs-offset="0,1" aria-expanded="false">
                <span class="sa-toolbar-user__avatar sa-symbol sa-symbol--shape--rounded">
                    <img src="{{ asset('assets/images/customers/customer-4-64x64.jpg') }}" width="64" height="64" alt=""/>
                </span>
                <span class="sa-toolbar-user__info">
                    <span class="sa-toolbar-user__title">{{ Auth::user()?->name ?? 'Guest' }}</span>
                    <span class="sa-toolbar-user__subtitle">{{ Auth::user()?->email ?? 'guest@example.com' }}</span>
                </span>
            </button>
            <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton">
                <li><a class="dropdown-item" href="{{ route('profile.index') }}">Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('profile.index') }}">Settings</a></li>
                <li><hr class="dropdown-divider"/></li>
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST" id="logoutForm">
                        @csrf
                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">Sign Out</a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    <div class="sa-toolbar__shadow"></div>
</div>
<!-- sa-app__toolbar / end -->

