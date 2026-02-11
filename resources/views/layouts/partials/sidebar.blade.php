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
        @if(!request()->routeIs('sections.*'))  
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
                        
                        @if(auth()->user()->hasPermission('product_master.view'))
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
                        @endif
                         
                        @if(
                            auth()->user()->hasPermission('brands.view') ||
                            auth()->user()->hasPermission('categories.view') ||
                            auth()->user()->hasPermission('units.view')
                        )
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
                                @if(auth()->user()->hasPermission('brands.view'))
                                <li class="sa-nav__menu-item {{ request()->routeIs('brands.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('brands.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__title">Brands</span>
                                    </a>
                                </li>
                                @endif
                                @if(auth()->user()->hasPermission('categories.view'))
                                <li class="sa-nav__menu-item {{ request()->routeIs('categories.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('categories.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__title">Categories</span>
                                    </a>
                                </li>
                                @endif 
                                @if(auth()->user()->hasPermission('units.view'))
                                <li class="sa-nav__menu-item {{ request()->routeIs('units.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('units.index') }}" class="sa-nav__link"> 
                                        <span class="sa-nav__title">Units</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif
                        
                        @if(auth()->user()->hasPermission('warehouse.view'))
                        <!-- Warehouses -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('warehouses.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('warehouses.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-building'></i>
                                </span> 
                                <span class="sa-nav__title">Warehouses</span>
                            </a>
                        </li>
                        @endif
                        
                        @if(
                            auth()->user()->hasPermission('shipping_zones.view') ||
                            auth()->user()->hasPermission('shipping_methods.view') ||
                            auth()->user()->hasPermission('shipping_rates.view')
                        )
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
                                @if(auth()->user()->hasPermission('shipping_zones.view'))
                                <li class="sa-nav__menu-item {{ request()->routeIs('shipping.zones.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('shipping.zones.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Zones</span>
                                    </a>
                                </li>
                                @endif
                                @if(auth()->user()->hasPermission('shipping_methods.view'))
                                <li class="sa-nav__menu-item {{ request()->routeIs('shipping.methods.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('shipping.methods.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Methods</span>
                                    </a>
                                </li>
                                @endif
                                @if(auth()->user()->hasPermission('shipping_rates.view'))
                                <li class="sa-nav__menu-item {{ request()->routeIs('shipping.rates.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('shipping.rates.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Rates</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li> 
                        @endif

                         @if(auth()->user()->hasPermission('inventory.view'))
                        <!-- Inventory -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('inventory.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('inventory.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-package'></i>
                                </span> 
                                <span class="sa-nav__title">Inventory</span>
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->hasPermission('order.view'))
                        <!-- Orders -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('orders.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('orders.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-cart'></i>
                                </span>
                                <span class="sa-nav__title">Orders</span>
                            </a>
                        </li>
                        @endif
                        
                        @if(auth()->user()->hasPermission('carts.view'))
                        <!-- Carts -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('carts.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('carts.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-shopping-bag'></i>
                                </span>
                                <span class="sa-nav__title">Carts</span>
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('coupons.view'))
                        <!-- Coupons -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('coupons.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('coupons.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <i class='bx bx-purchase-tag'></i>
                                </span>
                                <span class="sa-nav__title">Coupons</span>
                            </a>
                        </li>
                        @endif
                        
                        @if(
                            auth()->user()->hasPermission('customer.view') ||
                            auth()->user()->hasPermission('field_management.view')  
                        )
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
                                @if(auth()->user()->hasPermission('customer.view'))
                                <li class="sa-nav__menu-item {{ request()->routeIs('customers.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('customers.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Customers</span>
                                    </a>
                                </li> 
                                @endif
                                @if(auth()->user()->hasPermission('field_management.view'))
                                <li class="sa-nav__menu-item {{ request()->routeIs('field-management.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('field-management.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Field Management</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif
                        
                        @if(
                            auth()->user()->hasPermission('leads.view') ||
                            auth()->user()->hasPermission('lead_masters.view')
                        )
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
                                @if(auth()->user()->hasPermission('leads.view'))
                                <li class="sa-nav__menu-item {{ request()->routeIs('leads.index') || (request()->routeIs('leads.*') && !request()->routeIs('lead-masters.*')) ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('leads.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Leads</span>
                                    </a>
                                </li>
                                @endif
                                @if(auth()->user()->hasPermission('lead_masters.view'))
                                <li class="sa-nav__menu-item {{ request()->routeIs('lead-masters.*') ? 'sa-nav__menu-item--active' : '' }}">
                                    <a href="{{ route('lead-masters.index') }}" class="sa-nav__link">
                                        <span class="sa-nav__menu-item-padding"></span>
                                        <span class="sa-nav__title">Lead Masters</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif

                    </ul>
                </li>
                
              
                @if(
                    auth()->user()->hasPermission('user.view') ||
                    auth()->user()->hasPermission('role_permission.view')
                ) 
                <!-- User Management Menu -->
                <li class="sa-nav__section">
                    <div class="sa-nav__section-title"><span>User Management</span></div>
                    <ul class="sa-nav__menu sa-nav__menu--root">
                        @if(auth()->user()->hasPermission('user.view'))
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
                        @endif
                        @if(auth()->user()->hasPermission('role_permission.view'))
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
                        @endif
                    </ul>
                </li>
                @endif
                
                <!-- Settings Menu -->
                @if(
                    auth()->user()->hasPermission('company_profile.view') ||
                    auth()->user()->hasPermission('integrations.view') ||   
                    auth()->user()->hasPermission('contact_messages.view')  
                )    
                <li class="sa-nav__section">
                    <div class="sa-nav__section-title"><span>Settings</span></div>
                    <ul class="sa-nav__menu sa-nav__menu--root">
                        
                        @if(auth()->user()->hasPermission('company_profile.view'))
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
                        @endif
                        
                        @if(auth()->user()->hasPermission('section_management.view')) 
                        <!-- Section Management -->
                        <li class=" sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('sections.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('sections.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M2,0h12c1.1,0,2,0.9,2,2v12c0,1.1-0.9,2-2,2H2c-1.1,0-2-0.9-2-2V2C0,0.9,0.9,0,2,0z M2,2v4h12V2H2z M2,8v2h5V8H2z M9,8v2h5V8H9z M2,12v2h12v-2H2z"></path>
                                    </svg>
                                </span>
                                <span class="sa-nav__title">Section Management</span>
                            </a>
                        </li> 
                        @endif 

                        @if(auth()->user()->hasPermission('integrations.view'))
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
                        @endif

                        @if(auth()->user()->hasPermission('contact_messages.view'))
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
                        @endif
 
                         <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('themes.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('themes.index') }}" class="sa-nav__link">
                                <span class="sa-nav__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M8,9c2.206,0,4-1.794,4-4s-1.794-4-4-4S4,2.794,4,5S5.794,9,8,9z M8,3c1.103,0,2,0.897,2,2s-0.897,2-2,2S6,6.103,6,5 S6.897,3,8,3z M8,10c-3.314,0-6,2.686-6,6h2c0-2.206,1.794-4,4-4s4,1.794,4,4h2C14,12.686,11.314,10,8,10z"></path>
                                    </svg>
                                </span> 
                                <span class="sa-nav__title">Themes</span>
                            </a>
                        </li>  

                    </ul>
                </li>
                @endif


                @php
                    $activeSectionIds = \App\Models\Section::where('is_active', true)
                        ->pluck('section_id')
                        ->toArray();

                    $sliderSections = [
                        'issliderbanner-v1',
                        'issliderbanner-v2',
                    ];

                    $featuredCategorySections = [
                        'isfeaturedcategory-v1',
                        'isfeaturedcategory-v2',
                        'isfeaturedcategory-v3',
                        'isfeaturedcategory-v4',
                        'isfeaturedcategory-v5',
                        'isfeaturedcategory-v6',
                    ];

                    $ourCollectionSections = [
                        'isourcollection-v1',
                        'isourcollection-v2',
                    ];

                    $istestimonialSections = [
                        'istestimonials-v1',
                    ];
                    
                    $blogSections = [
                        'isblog-v1',
                    ];

                    $instagramGallerySections = [
                        'isinstagram-v1',
                    ];

                    $serviceHighlightsSections = [
                        'ishighlights-v1',
                    ];

                @endphp

                @if(auth()->user()->hasPermission('website_management.view'))
                <!-- Website Management Menu -->
                <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('website.*') ?  (request()->routeIs('home-sliders.*') || request()->routeIs('featured-category-style.*') || request()->routeIs('our-collection.*') || request()->routeIs('testimonials.*') || request()->routeIs('instagram-gallery.*') || request()->routeIs('reviews.*') || request()->routeIs('service-highlights.*') ? 'sa-nav__menu-item--open' : '') : '' }}" data-sa-collapse-item="sa-nav__menu-item--open" style="margin-bottom: 8px;">
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

                    <!-- Slider Sections -->
                    @if(array_intersect($sliderSections, $activeSectionIds))
                        <li class="sa-nav__menu-item {{ request()->routeIs('home-sliders.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('home-sliders.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Home Sliders</span>
                            </a>
                        </li>
                        @endif

                        @if(array_intersect($featuredCategorySections, $activeSectionIds))
                        <li class="sa-nav__menu-item {{ request()->routeIs('featured-category-style.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('featured-category-style.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Featured Category Style</span>
                            </a>
                        </li>
                        @endif

                        @if(array_intersect($ourCollectionSections, $activeSectionIds)) 
                        <li class="sa-nav__menu-item {{ request()->routeIs('our-collection.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('our-collection.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Our Collection Section</span>
                            </a>
                        </li>
                        @endif

                        @if(array_intersect($istestimonialSections, $activeSectionIds))
                        <li class="sa-nav__menu-item {{ request()->routeIs('testimonials.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('testimonials.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Testimonials</span>
                            </a>
                        </li>
                        @endif

                        @if(array_intersect($instagramGallerySections, $activeSectionIds))
                        <li class="sa-nav__menu-item {{ request()->routeIs('instagram-gallery.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('instagram-gallery.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Instagram Gallery</span>
                            </a>
                        </li>
                        @endif

                        @if(array_intersect($serviceHighlightsSections, $activeSectionIds))
                        <li class="sa-nav__menu-item {{ request()->routeIs('service-highlights.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('service-highlights.index') }}" class="sa-nav__link">
                                <span class="sa-nav__menu-item-padding"></span>
                                <span class="sa-nav__title">Service Highlights</span>
                            </a>
                        </li> 
                        @endif
                        
                        <!-- Reviews -->
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('reviews.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('reviews.index') }}" class="sa-nav__link"> 
                                <span class="sa-nav__menu-item-padding"></span> 
                                <span class="sa-nav__title">Reviews Management</span>
                            </a>
                        </li>

                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('about-us.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('about-us.index') }}" class="sa-nav__link"> 
                                <span class="sa-nav__menu-item-padding"></span> 
                                <span class="sa-nav__title">About Us</span>
                            </a>
                        </li>

                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('legal-pages.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('legal-pages.index') }}" class="sa-nav__link"> 
                                <span class="sa-nav__menu-item-padding"></span> 
                                <span class="sa-nav__title">Legal Pages</span>
                            </a>
                        </li>

                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('faqs.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('faqs.index') }}" class="sa-nav__link"> 
                            <span class="sa-nav__menu-item-padding"></span> 
                                <span class="sa-nav__title">FAQs</span>
                            </a>
                        </li>


                        @if(array_intersect($blogSections, $activeSectionIds))
                        <li class="sa-nav__menu-item sa-nav__menu-item--has-icon {{ request()->routeIs('blogs.*') ? 'sa-nav__menu-item--active' : '' }}">
                            <a href="{{ route('blogs.index') }}" class="sa-nav__link"> 
                                <span class="sa-nav__menu-item-padding"></span> 
                                <span class="sa-nav__title">Blogs</span>
                            </a>
                        </li>
                        @endif


                        
                    </ul>
                </li> 
                @endif
            </ul>
        </div>
        @else
        <div class="sa-sidebar__body" data-simplebar="">
    <ul class="sa-nav sa-nav--sidebar" data-sa-collapse="" id="sectionsSidebarList">

        <li class="sa-nav__section">
            <div class="sa-nav__section-title">
                <span>Sections</span>
            </div>
        </li>

        @if(isset($groupedSections))
            @foreach($groupedSections as $group)
                <li class="sa-nav__menu-item sa-nav__menu-item--has-icon"
                    data-section-base="{{ $group['base_name'] }}">

                    <div class="w-100" style="padding: 0.5rem 1rem;">

                        <!-- Row 1: Drag Handle + Title -->
                        <div class="d-flex align-items-center">
                            @if(!empty($group['show_sorting_btn']))
                            <button type="button"
                                    class="btn btn-sm p-0 me-2 section-sort-handle"
                                    title="Drag to reorder">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     width="16" height="16"
                                     viewBox="0 0 16 16"
                                     fill="currentColor"
                                     class="section-drag-icon">
                                    <path d="M2,6h12v1H2V6z M2,9h12v1H2V9z M2,3h12v1H2V3z M2,12h12v1H2V12z"></path>
                                </svg>
                            </button>
                            @else
                            <span class="me-2" style="width: 24px;"></span>
                            @endif

                            <span class="sa-nav__title flex-grow-1 sections-item-title">
                                {{ $group['display_name'] }}
                            </span>
                        </div>

                        <!-- Row 2: Variants listed directly -->
                        <div class="mt-1 ps-4 sections-variants-list">
                            @foreach($group['variants'] as $variant)
                                <div class="d-flex align-items-center justify-content-between sections-variant-row py-1">
                                    <div class="flex-grow-1">
                                        <span class="sections-variant-title">{{ $variant->title }}</span>
                                        <small class="sections-variant-id d-block">{{ $variant->section_id }}</small>
                                    </div>
                                    <div class="form-check form-switch ms-2 mb-0">
                                        <input class="form-check-input section-toggle"
                                               type="checkbox"
                                               data-section-id="{{ $variant->id }}"
                                               {{ $variant->is_active ? 'checked' : '' }}
                                               id="toggle{{ $variant->id }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </li>
            @endforeach
        @endif

    </ul>
</div>

        
        <style>
            /* Sections sidebar: high contrast on dark background */
            #sectionsSidebarList {
                list-style: none;
                padding-left: 0;
            }
            #sectionsSidebarList .sa-nav__section-title {
                color: rgba(255, 255, 255, 0.7);
                font-size: 0.7rem;
                font-weight: 600;
                letter-spacing: 0.05em;
                padding: 0.5rem 1rem;
                text-transform: uppercase;
            }
            #sectionsSidebarList .sa-nav__menu-item {
                border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            }
            #sectionsSidebarList .sa-nav__menu-item:last-child {
                border-bottom: none;
            }
            #sectionsSidebarList .sections-item-title {
                color: #fff;
                font-weight: 600;
            }
            /* Drag handle: visible on dark sidebar */
            #sectionsSidebarList .section-sort-handle {
                cursor: move;
                border: none;
                background: none;
                padding: 0.25rem;
                color: rgba(255, 255, 255, 0.7);
                line-height: 0;
            }
            #sectionsSidebarList .section-sort-handle:hover {
                color: #fff;
            }
            #sectionsSidebarList .section-drag-icon {
                opacity: 1;
                display: block;
            }
            /* Variants list: visible on dark sidebar */
            #sectionsSidebarList .sections-variants-list {
                border-left: 1px solid rgba(255, 255, 255, 0.15);
            }
            #sectionsSidebarList .sections-variant-row {
                padding-left: 0.5rem;
            }
            #sectionsSidebarList .sections-variant-title {
                color: rgba(255, 255, 255, 0.9);
                font-size: 0.8rem;
                font-weight: 500;
            }
            #sectionsSidebarList .sections-variant-id {
                color: rgba(255, 255, 255, 0.5);
                font-size: 0.7rem;
            }
            #sectionsSidebarList .section-toggle {
                cursor: pointer;
            }
        </style>
        
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Refresh frontend preview iframe (only on sections page)
                function refreshFrontendPreview() {
                    var iframe = document.getElementById('frontendPreview');
                    if (iframe && iframe.src) {
                        iframe.src = iframe.src;
                    }
                }

                // Toggle active/inactive
                document.querySelectorAll('.section-toggle').forEach(function(toggle) {
                    toggle.addEventListener('change', function() {
                        const sectionId = this.dataset.sectionId;
                        const isActive = this.checked;
                        if (typeof window.showPageSpinner === 'function') window.showPageSpinner();
                        
                        fetch('{{ route("sections.toggleVariant") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                section_id: sectionId,
                                is_active: isActive
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                refreshFrontendPreview();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.checked = !isActive; // Revert on error
                        })
                        .finally(function() {
                            if (typeof window.hidePageSpinner === 'function') window.hidePageSpinner();
                        });
                    });
                });
                
                // Sortable functionality
                const sortableList = document.getElementById('sectionsSidebarList');
                if (sortableList && typeof Sortable !== 'undefined') {
                    new Sortable(sortableList, {
                        handle: '.section-sort-handle',
                        animation: 150,
                        filter: '.sa-nav__section', // Don't allow dragging the section title
                        onEnd: function(evt) {
                            const items = Array.from(sortableList.querySelectorAll('[data-section-base]'));
                            const sortData = [];
                            let currentOrder = 1;
                            
                            items.forEach((item) => {
                                const baseName = item.dataset.sectionBase;
                                const variants = Array.from(item.querySelectorAll('.section-toggle'));
                                
                                // Get variant IDs and update their sort orders
                                variants.forEach(toggle => {
                                    sortData.push({
                                        id: parseInt(toggle.dataset.sectionId),
                                        sort_order: currentOrder
                                    });
                                    currentOrder++;
                                });
                            });
                            
                            if (typeof window.showPageSpinner === 'function') window.showPageSpinner();
                            
                            fetch('{{ route("sections.updateSortOrder") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    items: sortData
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    refreshFrontendPreview();
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            })
                            .finally(function() {
                                if (typeof window.hidePageSpinner === 'function') window.hidePageSpinner();
                            });
                        }
                    });
                }
            });
        </script>
        @endif
    </div>
    <div class="sa-app__sidebar-shadow"></div>
    <div class="sa-app__sidebar-backdrop" data-sa-close-sidebar=""></div>
</div>
<!-- sa-app__sidebar / end -->

