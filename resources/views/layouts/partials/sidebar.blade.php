<!-- sa-app__sidebar -->
<div class="sa-app__sidebar">
    <div class="sa-sidebar">
        <div class="sa-sidebar__header">
            <a class="sa-sidebar__logo" href="{{ route('dashboard') }}">
                <!-- logo -->
                <div class="sa-sidebar-logo"> 
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
                                <span class="sa-nav__title">Product Master</span>
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
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('master-data.*') || request()->routeIs('attributes.*') || request()->routeIs('brands.*') || request()->routeIs('categories.*') || request()->routeIs('units.*') ? 'sa-nav__menu-item--open' : '' }}" data-sa-collapse-item="sa-nav__menu-item--open">
                            <a href="#" class="sa-nav__link" data-sa-collapse-trigger="">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M8,0C3.6,0,0,3.6,0,8s3.6,8,8,8s8-3.6,8-8S12.4,0,8,0z M8,14c-3.3,0-6-2.7-6-6s2.7-6,6-6s6,2.7,6,6S11.3,14,8,14z"></path>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">Product Categorization </span>
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
                                <!-- <li class="sa-nav__menu-item {{ request()->routeIs('attributes.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('attributes.index') }}" class="sa-nav__link"> 
                                        <span class="sa-nav__title">Attributes</span>
                                    </a>
                                </li> -->
                                <li class="sa-nav__menu-item {{ request()->routeIs('units.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('units.index') }}" class="sa-nav__link"> 
                                        <span class="sa-nav__title">Units</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Warehouses -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('warehouses.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('warehouses.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-building'></i>
                                </span> 
                                <span class="sa-nav__title">Warehouses</span>
                            </a>
                        </li>
                        
                        <!-- Shipping Menu -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('shipping.*') ? 'sa-nav__menu-item--open' : '' }}" data-sa-collapse-item="sa-nav__menu-item--open">
                            <a href="#" class="sa-nav__link" data-sa-collapse-trigger="">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-box'></i> 
                                </span>
                                <span class="sa-nav__title">Shipping</span>
                                <span class="sa-nav__arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="6" height="9" viewBox="0 0 6 9" fill="currentColor">
                                        <path d="M5.605,0.213 C6.007,0.613 6.107,1.212 5.706,1.612 L2.696,4.511 L5.706,7.409 C6.107,7.809 6.107,8.509 5.605,8.808 C5.204,9.108 4.702,9.108 4.301,8.709 L-0.013,4.511 L4.401,0.313 C4.702,-0.087 5.304,-0.087 5.605,0.213 Z"></path>
                                    </svg>
                                </span>
                            </a>
                            <ul class="sa-nav__menu sa-nav__menu--sub" data-sa-collapse-content="">
                                <li class="sa-nav__menu-item {{ request()->routeIs('shipping.zones.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('shipping.zones.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Zones</span>
                                    </a>
                                </li>
                                <li class="sa-nav__menu-item {{ request()->routeIs('shipping.methods.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('shipping.methods.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Methods</span>
                                    </a>
                                </li>
                                <li class="sa-nav__menu-item {{ request()->routeIs('shipping.rates.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('shipping.rates.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Rates</span>
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
                        <li class="d-none sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('sections.*') ? 'sa-nav__menu-item--active' : '' }}">
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
                                <span class="sa-nav__title">Company Profile</span>
                            </a>
                        </li>
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('integrations.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('integrations.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M15,7H9V1c0-0.6-0.4-1-1-1S7,0.4,7,1v6H1C0.4,7,0,7.4,0,8s0.4,1,1,1h6v6c0,0.6,0.4,1,1,1s1-0.4,1-1V9h6c0.6,0,1-0.4,1-1S15.6,7,15,7z"></path>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">Integrations</span>
                            </a>
                        </li>
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('contacts.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('contacts.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M14,0H2C0.9,0,0,0.9,0,2v10c0,1.1,0.9,2,2,2h5l-2,2h6l-2-2h5c1.1,0,2-0.9,2-2V2C16,0.9,15.1,0,14,0z M14,12H2V2h12V12z M8,3 L8,3c-1.7,0-3,1.3-3,3v1c0,1.7,1.3,3,3,3h0c1.7,0,3-1.3,3-3V6C11,4.3,9.7,3,8,3z M9,7c0,0.6-0.4,1-1,1s-1-0.4-1-1V6 c0-0.6,0.4-1,1-1s1,0.4,1,1V7z"></path>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">Contact Messages</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Website Management Menu -->
                <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('website.*') ?  (request()->routeIs('home-sliders.*') || request()->routeIs('featured-category-style.*') || request()->routeIs('our-collection.*') || request()->routeIs('testimonials.*') || request()->routeIs('reviews.*') || request()->routeIs('service-highlights.*') ? 'sa-nav__menu-item--open' : '') : '' }}" data-sa-collapse-item="sa-nav__menu-item--open" style="margin-bottom: 8px;">
                    <a href="#" class="sa-nav__link" data-sa-collapse-trigger="">
                        <span class="sa-nav__icon">
                            <i class='bx bx-globe'></i>
                        </span>
                        <span class="sa-nav__title">Website Management</span>
                        <span class="sa-nav__arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="6" height="9" viewBox="0 0 6 9" fill="currentColor">
                                <path d="M5.605,0.213 C6.007,0.613 6.107,1.212 5.706,1.612 L2.696,4.511 L5.706,7.409 C6.107,7.809 6.107,8.509 5.605,8.808 C5.204,9.108 4.702,9.108 4.301,8.709 L-0.013,4.511 L4.401,0.313 C4.702,-0.087 5.304,-0.087 5.605,0.213 Z"></path>
                            </svg>
                        </span>
                    </a>

                    <ul class="sa-nav__menu sa-nav__menu--sub" data-sa-collapse-content="">
                       

                    <li class="sa-nav__menu-item {{ request()->routeIs('home-sliders.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('home-sliders.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Home Sliders</span>
                            </a>
                        </li>
                    <li class="sa-nav__menu-item {{ request()->routeIs('featured-category-style.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('featured-category-style.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Featured Category Style</span>
                            </a>
                        </li>

                        <li class="sa-nav__menu-item {{ request()->routeIs('our-collection.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('our-collection.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Our Collection</span>
                            </a>
                        </li>

                        <li class="sa-nav__menu-item {{ request()->routeIs('testimonials.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('testimonials.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Testimonials</span>
                            </a>
                        </li>


                        <li class="sa-nav__menu-item {{ request()->routeIs('service-highlights.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('service-highlights.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Service Highlights</span>
                            </a>
                        </li> 

                        <!-- Reviews -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('reviews.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('reviews.index') }}" class="sa-nav__link">
                                <!-- <span class="sa-nav__icon">
                                    <i class='bx bx-star'></i>
                                </span> -->
                                <span class="sa-nav__menu-item-padding"></span> 
                                <span class="sa-nav__title">Reviews Management</span>
                            </a>
                        </li>

                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('about-us.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('about-us.index') }}" class="sa-nav__link">
                                <!-- <span class="sa-nav__icon">
                                    <i class='bx bx-info-circle'></i>
                                </span> -->
                                <span class="sa-nav__menu-item-padding"></span> 
                                <span class="sa-nav__title">About Us</span>
                            </a>
                        </li>

                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('legal-pages.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('legal-pages.index') }}" class="sa-nav__link">
                                <!-- <span class="sa-nav__icon">
                                    <i class='bx bx-file-blank'></i>
                                </span> -->
                                <span class="sa-nav__menu-item-padding"></span> 
                                <span class="sa-nav__title">Legal Pages</span>
                            </a>
                        </li>

                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('faqs.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('faqs.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9.09 9C9.3251 8.33167 9.78915 7.76811 10.4 7.40913C11.0108 7.05016 11.7289 6.91894 12.4272 7.03871C13.1255 7.15849 13.7588 7.52152 14.2151 8.06353C14.6713 8.60553 14.9211 9.29152 14.92 10C14.92 12 11.92 13 11.92 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">FAQs</span>
                            </a>
                        </li>

                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('blogs.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('blogs.index') }}" class="sa-nav__link">
                                <!-- <span class="sa-nav__icon">
                                    <i class='bx bx-news'></i>
                                </span> -->
                                <span class="sa-nav__menu-item-padding"></span> 
                                <span class="sa-nav__title">Blogs</span>
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

