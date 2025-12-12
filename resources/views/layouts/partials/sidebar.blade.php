<!-- sa-app__sidebar -->
<div class="sa-app__sidebar">
    <div class="sa-sidebar">
        <div class="sa-sidebar__header">
            <a class="sa-sidebar__logo" href="{{ route('dashboard') }}">
                <!-- logo -->
                <div class="sa-sidebar-logo">
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" width="120px" height="20px">
                        <path d="M118.5,20h-1.1c-0.6,0-1.2-0.4-1.4-1l-1.5-4h-6.1l-1.5,4c-0.2,0.6-0.8,1-1.4,1h-1.1c-1,0-1.8-1-1.4-2l1.1-3l1.5-4l3.6-10c0.2-0.6,0.8-1,1.4-1h1.6c0.6,0,1.2,0.4,1.4,1l3.6,10l1.5,4l1.1,3C120.3,19,119.5,20,118.5,20z M111.5,6.6l-1.6,4.4h3.2L111.5,6.6z M99.5,20h-1.4c-0.4,0-0.7-0.2-0.9-0.5L94,14l-2,3.5v1c0,0.8-0.7,1.5-1.5,1.5h-1c-0.8,0-1.5-0.7-1.5-1.5v-17C88,0.7,88.7,0,89.5,0h1C91.3,0,92,0.7,92,1.5v8L94,6l3.2-5.5C97.4,0.2,97.7,0,98.1,0h1.4c1.2,0,1.9,1.3,1.3,2.3L96.3,10l4.5,7.8C101.4,18.8,100.7,20,99.5,20z M80.3,11.8L80,12.3v6.2c0,0.8-0.7,1.5-1.5,1.5h-1c-0.8,0-1.5-0.7-1.5-1.5v-6.2l-0.3-0.5l-5.5-9.5c-0.6-1,0.2-2.3,1.3-2.3h1.4c0.4,0,0.7,0.2,0.9,0.5L76,4.3l2,3.5l2-3.5l2.2-3.8C82.4,0.2,82.7,0,83.1,0h1.4c1.2,0,1.9,1.3,1.3,2.3L80.3,11.8z M60,20c-5.5,0-10-4.5-10-10S54.5,0,60,0s10,4.5,10,10S65.5,20,60,20z M60,4c-3.3,0-6,2.7-6,6s2.7,6,6,6s6-2.7,6-6S63.3,4,60,4z M47.8,17.8c0.6,1-0.2,2.3-1.3,2.3h-2L41,14h-4v4.5c0,0.8-0.7,1.5-1.5,1.5h-1c-0.8,0-1.5-0.7-1.5-1.5v-17C33,0.7,33.7,0,34.5,0H41c0.3,0,0.7,0,1,0.1c3.4,0.5,6,3.4,6,6.9c0,2.4-1.2,4.5-3.1,5.8L47.8,17.8z M42,4.2C41.7,4.1,41.3,4,41,4h-3c-0.6,0-1,0.4-1,1v4c0,0.6,0.4,1,1,1h3c0.3,0,0.7-0.1,1-0.2c0.3-0.1,0.6-0.3,0.9-0.5C43.6,8.8,44,7.9,44,7C44,5.7,43.2,4.6,42,4.2z M29.5,4H25v14.5c0,0.8-0.7,1.5-1.5,1.5h-1c-0.8,0-1.5-0.7-1.5-1.5V4h-4.5C15.7,4,15,3.3,15,2.5v-1C15,0.7,15.7,0,16.5,0h13C30.3,0,31,0.7,31,1.5v1C31,3.3,30.3,4,29.5,4z M6.5,20c-2.8,0-5.5-1.7-6.4-4c-0.4-1,0.3-2,1.3-2h1c0.5,0,0.9,0.3,1.2,0.7c0.2,0.3,0.4,0.6,0.8,0.8C4.9,15.8,5.8,16,6.5,16c1.5,0,2.8-0.9,2.8-2c0-0.7-0.5-1.3-1.2-1.6C7.4,12,7,11,7.4,10.3l0.4-0.9c0.4-0.7,1.2-1,1.8-0.6c0.6,0.3,1.2,0.7,1.6,1.2c1,1.1,1.7,2.5,1.7,4C13,17.3,10.1,20,6.5,20z M11.6,6h-1c-0.5,0-0.9-0.3-1.2-0.7C9.2,4.9,8.9,4.7,8.6,4.5C8.1,4.2,7.2,4,6.5,4C5,4,3.7,4.9,3.7,6c0,0.7,0.5,1.3,1.2,1.6C5.6,8,6,9,5.6,9.7l-0.4,0.9c-0.4,0.7-1.2,1-1.8,0.6c-0.6-0.3-1.2-0.7-1.6-1.2C0.6,8.9,0,7.5,0,6c0-3.3,2.9-6,6.5-6c2.8,0,5.5,1.7,6.4,4C13.3,4.9,12.6,6,11.6,6z"></path>
                    </svg> -->
                    <div class="sa-sidebar-logo__caption">Admin</div>
                </div>
                <!-- logo / end -->
            </a>
        </div>
        <div class="sa-sidebar__body" data-simplebar="">
            <ul class="sa-nav sa-nav--sidebar" data-sa-collapse="">
                <li class="sa-nav__section">
                    <div class="sa-nav__section-title"><span>Application</span></div>
                    <ul class="sa-nav__menu sa-nav__menu--root">
                        <!-- Dashboard -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('dashboard') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('dashboard') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M8,13.1c-4.4,0-8,3.4-8-3C0,5.6,3.6,2,8,2s8,3.6,8,8.1C16,16.5,12.4,13.1,8,13.1zM8,4c-3.3,0-6,2.7-6,6c0,4,2.4,0.9,5,0.2C7,9.9,7.1,9.5,7.4,9.2l3-2.3c0.4-0.3,1-0.2,1.3,0.3c0.3,0.5,0.2,1.1-0.2,1.4l-2.2,1.7c2.5,0.9,4.8,3.6,4.8-0.2C14,6.7,11.3,4,8,4z"></path>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">Dashboard</span>
                            </a>
                        </li>
                        
                        <!-- Catalog Menu -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('products.*') ? 'sa-nav__menu-item--open' : '' }}" data-sa-collapse-item="sa-nav__menu-item--open">
                            <a href="#" class="sa-nav__link" data-sa-collapse-trigger="">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M8,6C4.7,6,2,4.7,2,3s2.7-3,6-3s6,1.3,6,3S11.3,6,8,6z M2,5L2,5L2,5C2,5,2,5,2,5z M8,8c3.3,0,6-1.3,6-3v3c0,1.7-2.7,3-6,3S2,9.7,2,8V5C2,6.7,4.7,8,8,8z M14,5L14,5C14,5,14,5,14,5L14,5z M2,10L2,10L2,10C2,10,2,10,2,10z M8,13c3.3,0,6-1.3,6-3v3c0,1.7-2.7,3-6,3s-6-1.3-6-3v-3C2,11.7,4.7,13,8,13z M14,10L14,10C14,10,14,10,14,10L14,10z"></path>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">Store</span>
                                <span class="sa-nav__arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="6" height="9" viewBox="0 0 6 9" fill="currentColor">
                                        <path d="M5.605,0.213 C6.007,0.613 6.107,1.212 5.706,1.612 L2.696,4.511 L5.706,7.409 C6.107,7.809 6.107,8.509 5.605,8.808 C5.204,9.108 4.702,9.108 4.301,8.709 L-0.013,4.511 L4.401,0.313 C4.702,-0.087 5.304,-0.087 5.605,0.213 Z"></path>
                                    </svg>
                                </span>
                            </a>
                            <ul class="sa-nav__menu sa-nav__menu--sub" data-sa-collapse-content="">
                                <!-- give option to add product here also -->
                                <li class="sa-nav__menu-item {{ request()->routeIs('products.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('products.create') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Add Product</span>
                                    </a>
                                </li>
                                <li class="sa-nav__menu-item {{ request()->routeIs('products.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('products.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Products</span>
                                    </a>
                                </li>  
                            </ul>
                        </li>
                        
                        <!-- Master Data Menu -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('master-data.*') || request()->routeIs('attributes.*') || request()->routeIs('brands.*') || request()->routeIs('categories.*') || request()->routeIs('units.*') || request()->routeIs('warehouses.*') ? 'sa-nav__menu-item--open' : '' }}" data-sa-collapse-item="sa-nav__menu-item--open">
                            <a href="#" class="sa-nav__link" data-sa-collapse-trigger="">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M8,0C3.6,0,0,3.6,0,8s3.6,8,8,8s8-3.6,8-8S12.4,0,8,0z M8,14c-3.3,0-6-2.7-6-6s2.7-6,6-6s6,2.7,6,6S11.3,14,8,14z"></path>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">Master Data</span>
                                <span class="sa-nav__arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="6" height="9" viewBox="0 0 6 9" fill="currentColor">
                                        <path d="M5.605,0.213 C6.007,0.613 6.107,1.212 5.706,1.612 L2.696,4.511 L5.706,7.409 C6.107,7.809 6.107,8.509 5.605,8.808 C5.204,9.108 4.702,9.108 4.301,8.709 L-0.013,4.511 L4.401,0.313 C4.702,-0.087 5.304,-0.087 5.605,0.213 Z"></path>
                                    </svg>
                                </span>
                            </a>
                            <ul class="sa-nav__menu sa-nav__menu--sub" data-sa-collapse-content="">
                                <li class="sa-nav__menu-item {{ request()->routeIs('brands.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('brands.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__title">Brands</span>
                                    </a>
                                </li>
                                <li class="sa-nav__menu-item {{ request()->routeIs('categories.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('categories.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__title">Categories</span>
                                    </a>
                                </li>
                                <li class="sa-nav__menu-item {{ request()->routeIs('attributes.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('attributes.index') }}" class="sa-nav__link"> 
                                        <span class="sa-nav__title">Attributes</span>
                                    </a>
                                </li>
                                <li class="sa-nav__menu-item {{ request()->routeIs('units.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('units.index') }}" class="sa-nav__link"> 
                                        <span class="sa-nav__title">Units</span>
                                    </a>
                                </li>
                                <li class="sa-nav__menu-item {{ request()->routeIs('warehouses.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('warehouses.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__icon">
                                            <i class='bx bx-building'></i>
                                        </span>
                                        <span class="sa-nav__title">Warehouses</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Inventory -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('inventory.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('inventory.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-package'></i>
                                </span> 
                                <span class="sa-nav__title">Inventory</span>
                            </a>
                        </li>
                        
                        <!-- Orders -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('orders.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('orders.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-cart'></i>
                                </span>
                                <span class="sa-nav__title">Orders</span>
                            </a>
                        </li>
                        
                        <!-- Carts -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('carts.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('carts.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-shopping-bag'></i>
                                </span>
                                <span class="sa-nav__title">Carts</span>
                            </a>
                        </li>
                        
                        <!-- Coupons -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('coupons.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('coupons.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-purchase-tag'></i>
                                </span>
                                <span class="sa-nav__title">Coupons</span>
                            </a>
                        </li>
                        
                        <!-- Customer Management Menu -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('customers.*') || request()->routeIs('field-management.*') ? 'sa-nav__menu-item--open' : '' }}" data-sa-collapse-item="sa-nav__menu-item--open">
                            <a href="#" class="sa-nav__link" data-sa-collapse-trigger="">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-group'></i>
                                </span>
                                <span class="sa-nav__title">Customer Management</span>
                                <span class="sa-nav__arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="6" height="9" viewBox="0 0 6 9" fill="currentColor">
                                        <path d="M5.605,0.213 C6.007,0.613 6.107,1.212 5.706,1.612 L2.696,4.511 L5.706,7.409 C6.107,7.809 6.107,8.509 5.605,8.808 C5.204,9.108 4.702,9.108 4.301,8.709 L-0.013,4.511 L4.401,0.313 C4.702,-0.087 5.304,-0.087 5.605,0.213 Z"></path>
                                    </svg>
                                </span>
                            </a>
                            <ul class="sa-nav__menu sa-nav__menu--sub" data-sa-collapse-content="">
                                <li class="sa-nav__menu-item {{ request()->routeIs('customers.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('customers.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Customers</span>
                                    </a>
                                </li> 
                                <li class="sa-nav__menu-item {{ request()->routeIs('field-management.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('field-management.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Field Management</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Lead Management Menu -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('leads.*') || request()->routeIs('lead-masters.*') ? 'sa-nav__menu-item--open' : '' }}" data-sa-collapse-item="sa-nav__menu-item--open">
                            <a href="#" class="sa-nav__link" data-sa-collapse-trigger="">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-user-circle'></i>
                                </span>
                                <span class="sa-nav__title">Lead Management</span>
                                <span class="sa-nav__arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="6" height="9" viewBox="0 0 6 9" fill="currentColor">
                                        <path d="M5.605,0.213 C6.007,0.613 6.107,1.212 5.706,1.612 L2.696,4.511 L5.706,7.409 C6.107,7.809 6.107,8.509 5.605,8.808 C5.204,9.108 4.702,9.108 4.301,8.709 L-0.013,4.511 L4.401,0.313 C4.702,-0.087 5.304,-0.087 5.605,0.213 Z"></path>
                                    </svg>
                                </span>
                            </a>
                            <ul class="sa-nav__menu sa-nav__menu--sub" data-sa-collapse-content="">
                                <li class="sa-nav__menu-item {{ request()->routeIs('leads.index') || (request()->routeIs('leads.*') && !request()->routeIs('lead-masters.*')) ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('leads.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Leads</span>
                                    </a>
                                </li>
                                <li class="sa-nav__menu-item {{ request()->routeIs('lead-masters.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('lead-masters.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Lead Masters</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Section Management -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('sections.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('sections.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M2,0h12c1.1,0,2,0.9,2,2v12c0,1.1-0.9,2-2,2H2c-1.1,0-2-0.9-2-2V2C0,0.9,0.9,0,2,0z M2,2v4h12V2H2z M2,8v2h5V8H2z M9,8v2h5V8H9z M2,12v2h12v-2H2z"></path>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">Section Management</span>
                            </a>
                        </li>
                        
                    </ul>
                </li>
                
             
                <!-- User Management Menu -->
                <li class="sa-nav__section">
                    <div class="sa-nav__section-title"><span>User Management</span></div>
                    <ul class="sa-nav__menu sa-nav__menu--root">
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('users.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('users.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M8,9c2.206,0,4-1.794,4-4s-1.794-4-4-4S4,2.794,4,5S5.794,9,8,9z M8,3c1.103,0,2,0.897,2,2s-0.897,2-2,2S6,6.103,6,5 S6.897,3,8,3z M8,10c-3.314,0-6,2.686-6,6h2c0-2.206,1.794-4,4-4s4,1.794,4,4h2C14,12.686,11.314,10,8,10z"></path>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">Users</span>
                            </a>
                        </li>
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('roles.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('roles.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M14,0H2C0.9,0,0,0.9,0,2v10c0,1.1,0.9,2,2,2h5l-2,2h6l-2-2h5c1.1,0,2-0.9,2-2V2C16,0.9,15.1,0,14,0z M14,12H2V2h12V12z M8,3 L8,3c-1.7,0-3,1.3-3,3v1c0,1.7,1.3,3,3,3h0c1.7,0,3-1.3,3-3V6C11,4.3,9.7,3,8,3z M9,7c0,0.6-0.4,1-1,1s-1-0.4-1-1V6 c0-0.6,0.4-1,1-1s1,0.4,1,1V7z"></path>
                                    </svg>
                                </span> 
                                <span class="sa-nav__title">Role Permission</span> 
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Settings Menu -->
                <li class="sa-nav__section">
                    <div class="sa-nav__section-title"><span>Settings</span></div>
                    <ul class="sa-nav__menu sa-nav__menu--root">
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('profile.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('profile.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M8,9c2.206,0,4-1.794,4-4s-1.794-4-4-4S4,2.794,4,5S5.794,9,8,9z M8,3c1.103,0,2,0.897,2,2s-0.897,2-2,2S6,6.103,6,5 S6.897,3,8,3z M8,10c-3.314,0-6,2.686-6,6h2c0-2.206,1.794-4,4-4s4,1.794,4,4h2C14,12.686,11.314,10,8,10z"></path>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">Profile</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="sa-app__sidebar-shadow"></div>
    <div class="sa-app__sidebar-backdrop" data-sa-close-sidebar=""></div>
</div>
<!-- sa-app__sidebar / end -->

