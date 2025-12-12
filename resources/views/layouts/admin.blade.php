<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="format-detection" content="telephone=no"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'E-Commerce Admin') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}"/> 
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900,900i"/>
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.ltr.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/highlight.js/styles/github.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/simplebar/simplebar.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/air-datepicker/css/datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/css/dataTables.bootstrap5.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/nouislider/nouislider.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/fullcalendar/main.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}"/>
    
    <!-- FontAwesome -->
    <script src="{{ asset('assets/vendor/fontawesome/js/all.min.js') }}" defer></script>
    
    @stack('styles')
    <style>
        /* Page Loading Spinner */
        #pageLoadingSpinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s ease-out;
        }
        
        #pageLoadingSpinner.hide {
            opacity: 0;
            pointer-events: none;
        }
        
        #pageLoadingSpinner .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3em;
        }
        
        .bg-secondary {
	--bs-bg-opacity: 1;
	background-color: rgb(187, 187, 187) !important;
}


.select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option.select2-results__option--selected, .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option[aria-selected="true"]:not(.select2-results__option--highlighted) {
	color: #373737;
	background-color: #dedede !important;
}

.select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option.select2-results__option--highlighted {
	color: #373737;
	background-color: #dedede !important;
}


.select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option.select2-results__option--selected, .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option[aria-selected="true"]:not(.select2-results__option--highlighted) {
	color: #373737 !important;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered .select2-selection__choice {
	display: flex;
	flex-direction: row;
	align-items: center;
	padding: .35em .65em;
	margin-right: .375rem;
	margin-bottom: .375rem;
	font-size: 0.8rem !important;
	color: #212529;
	cursor: auto;
	border: 1px solid #f5c000;
	border-radius: .25rem;
	background: #ffc107 !important;
}
    </style>
</head>
<body>
    <!-- Page Loading Spinner -->
    <div id="pageLoadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- sa-app -->
    <div class="sa-app sa-app--desktop-sidebar-shown sa-app--mobile-sidebar-hidden sa-app--toolbar-fixed">
        
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')
        
        <!-- Main Content -->
        <div class="sa-app__content">
            
            <!-- Toolbar -->
            @include('layouts.partials.toolbar')
            
            <!-- Body -->
            <div id="top" class="sa-app__body px-2 px-lg-4">
                @yield('content')
            </div>
            
            <!-- Footer -->
            @include('layouts.partials.footer')
            
        </div>
        
        <!-- Toasts -->
        <div class="sa-app__toasts toast-container bottom-0 end-0"></div>
        
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/highlight.js/highlight.pack.js') }}"></script>
    <script src="{{ asset('assets/vendor/quill/quill.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/air-datepicker/js/datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/air-datepicker/js/i18n/datepicker.en.js') }}"></script>
    <script src="{{ asset('assets/vendor/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/fontawesome/js/all.min.js') }}" data-auto-replace-svg="" async=""></script>
    <script src="{{ asset('assets/vendor/chart.js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/nouislider/nouislider.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/fullcalendar/main.min.js') }}"></script>
    <script src="{{ asset('assets/js/stroyka.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    
    @stack('scripts')
    
    <!-- Page Loading Spinner Script -->
    <script>
        (function() {
            // Show spinner immediately
            const spinner = document.getElementById('pageLoadingSpinner');
            
            // Hide spinner when page is fully loaded
            window.addEventListener('load', function() {
                if (spinner) {
                    spinner.classList.add('hide');
                    // Remove from DOM after transition
                    setTimeout(function() {
                        spinner.style.display = 'none';
                    }, 300);
                }
            });
            
            // Also hide spinner when DOM is ready (fallback for fast loading)
            if (document.readyState === 'complete') {
                if (spinner) {
                    spinner.classList.add('hide');
                    setTimeout(function() {
                        spinner.style.display = 'none';
                    }, 300);
                }
            }
            
            // Global function to show/hide spinner manually if needed
            window.showPageSpinner = function() {
                if (spinner) {
                    spinner.style.display = 'flex';
                    spinner.classList.remove('hide');
                }
            };
            
            window.hidePageSpinner = function() {
                if (spinner) {
                    spinner.classList.add('hide');
                    setTimeout(function() {
                        spinner.style.display = 'none';
                    }, 300);
                }
            };
        })();
    </script>

    <!-- <script>
            $(document).ready(function () {
            const timeout = 5000;  //   5000 ms = 5 second 
            var idleTimer = null;
            $('*').bind('mousemove click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick', function () {
                clearTimeout(idleTimer);
        
                idleTimer = setTimeout(function () {
                    alert("5 second complete");
                    // document.getElementById('logout-form').submit();
                }, timeout);
            });
            $("body").trigger("mousemove");
        });
    </script> -->
</body>
</html>

