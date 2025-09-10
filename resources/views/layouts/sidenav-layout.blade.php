<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title></title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}" />
    <link href="{{ asset('assets/css/bootstrap.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/fontawesome.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/toastify.min.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/css/jquery.dataTables.min.css') }}" rel="stylesheet" />

    @stack('style')

</head>

<body>

    <div id="loader" class="LoadingOverlay d-none">
        <div class="Line-Progress">
            <div class="indeterminate"></div>
        </div>
    </div>

    <nav class="navbar fixed-top px-0 shadow-sm bg-white">
        <div class="container-fluid">

            <a class="navbar-brand" href="#">
                <span class="icon-nav m-0 h5" onclick="MenuBarClickHandler()">
                    <img class="nav-logo-sm mx-2" src="{{ asset('assets/images/menu.svg') }}" alt="logo" />
                </span>
                <img class="nav-logo  mx-2" src="{{ asset('assets/images/logo.png') }}" alt="logo" />
            </a>

            <div class="float-right h-auto d-flex">
                <div class="user-dropdown">
                    <img id="userAvatar" class="icon-nav-img" src="{{ asset('assets/images/user.webp') }}"
                        alt="" />
                    <div class="user-dropdown-content ">
                        <div class="mt-4 text-center">
                            <img id="userDropdownAvatar" class="icon-nav-img"
                                src="{{ asset('assets/images/user.webp') }}" alt="" />
                            <h6 id="loginUserName">User Name</h6>
                            <hr class="user-dropdown-divider  p-0" />
                        </div>
                        <a href="{{ route('profile') }}" class="side-bar-item">
                            <span class="side-bar-item-caption">Profile</span>
                        </a>
                        <a onclick="logout()" class="side-bar-item">
                            <span class="side-bar-item-caption">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>


    <div id="sideNavRef" class="side-nav-open">

        <div id="adminNav" style="display: none;">


            <a href="{{ route('dashboard') }}"
                class="side-bar-item {{ request()->routeIs('dashboard') ? 'side-bar-item-active' : '' }}">
                <i class="bi bi-graph-up"></i>
                <span class="side-bar-item-caption">Dashboard</span>
            </a>
            {{-- Assign Store Users --}}
            <a href="{{ route('store.users.index') }}" class="side-bar-item">
                <i class=" <i class="fas fa-user-cog""></i>
                <span class="side-bar-item-caption">Assign Store Users</span>
            </a>

            {{-- <a href="{{ route('invoices.show') }}" class="side-bar-item">
                <i class="bi bi-people"></i>
                <span class="side-bar-item-caption">Customer Orders</span>
            </a> --}}

            {{-- Category List --}}
            <a href="{{ route('categories.index') }}"
                class="side-bar-item {{ request()->routeIs('categories.*') ? 'side-bar-item-active' : '' }}">
                <i class="bi bi-list-nested"></i>
                <span class="side-bar-item-caption">Category</span>
            </a>

            <a href="{{ url('/backend/admin/products/list') }}" class="side-bar-item">
                <i class="bi bi-bag"></i>
                <span class="side-bar-item-caption">Product</span>
            </a>
            {{-- Product Create --}}
            <a href="{{ route('admin.products.adminProductCreate') }}" class="side-bar-item">
                <i class="bi bi-plus-circle"></i>
                <span class="side-bar-item-caption">Add Product</span>
            </a>

            {{-- Create Customer (NEW LINK) --}}
            <a href="{{ route('admin.customers.create') }}"
                class="side-bar-item {{ request()->routeIs('admin.customers.create') ? 'side-bar-item-active' : '' }}">
                <i class="bi bi-person-plus"></i>
                <span class="side-bar-item-caption">Create Customer</span>
            </a>

            {{-- Create Sale --}}
            <a href="{{ route('sales.page') }}" class="side-bar-item">
                <i class="bi bi-currency-dollar"></i>
                <span class="side-bar-item-caption">Create Sale</span>
            </a>

            {{-- Sales List --}}
            {{-- <a href="{{ route('sales.list') }}" class="side-bar-item">
                <i class="bi bi-list-check"></i>
                <span class="side-bar-item-caption">Sales List</span>
            </a> --}}

            {{-- Invoices List --}}
            <a href="{{ route('invoices.list') }}" class="side-bar-item">
                <i class="bi bi-receipt"></i>
                <span class="side-bar-item-caption">Invoices List</span>
            </a>

            {{-- Reports --}}
            <a href="{{ route('stock.report') }}" class="side-bar-item">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span class="side-bar-item-caption">Stock Report</span>
            </a>

            {{-- Orders --}}
            <a href="{{ route('orders.list') }}" class="side-bar-item">
                <i class="bi bi-cart4"></i>
                <span class="side-bar-item-caption">Orders</span>
            </a>


        </div>

        <div id="customerNav" style="display: none;">
            <a href="{{ route('dashboard') }}"
                class="side-bar-item {{ request()->routeIs('dashboard') ? 'side-bar-item-active' : '' }}">
                <i class="bi bi-graph-up"></i>
                <span class="side-bar-item-caption">Dashboard</span>
            </a>

            <a href=" {{ route('customer.order.list') }} " class="side-bar-item">
                <i class="bi bi-people"></i>
                <span class="side-bar-item-caption">Orders</span>
            </a>

            <a href="{{ route('customer.products') }}" class="side-bar-item">
                <i class="bi bi-bag"></i>
                <span class="side-bar-item-caption">Products</span>
            </a>

            <a href="" class="side-bar-item">
                <i class="bi bi-receipt"></i>
                <span class="side-bar-item-caption">Invoice</span>
            </a>
        </div>




    </div>


    <div id="contentRef" class="content">
        @yield('content')
    </div>


    <script src="{{ asset('assets/js/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>


    <script src="{{ asset('assets/js/toastify-js.js') }}"></script>
    {{-- <script src="{{asset('js/axios.min.js')}}"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.3/axios.min.js"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.js') }}"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let currentUserData = localStorage.getItem('user')

            if (currentUserData) {
                let currentUser = JSON.parse(currentUserData)

                let currentUserRole = currentUser.role

                if (currentUserRole == 'Admin') {
                    document.getElementById('adminNav').style.display = 'block'
                    document.getElementById('customerNav').style.display = 'none'
                } else {
                    document.getElementById('customerNav').style.display = 'block'
                    document.getElementById('adminNav').style.display = 'none'
                }

                document.getElementById('userAvatar').src = currentUser.image
                document.getElementById('userDropdownAvatar').src = currentUser.image
                document.getElementById('loginUserName').innerText = currentUser.name
            }

        })


        function MenuBarClickHandler() {
            let sideNav = document.getElementById('sideNavRef');
            let content = document.getElementById('contentRef');
            if (sideNav.classList.contains("side-nav-open")) {
                sideNav.classList.add("side-nav-close");
                sideNav.classList.remove("side-nav-open");
                content.classList.add("content-expand");
                content.classList.remove("content");
            } else {
                sideNav.classList.remove("side-nav-close");
                sideNav.classList.add("side-nav-open");
                content.classList.remove("content-expand");
                content.classList.add("content");
            }
        }


        async function logout() {
            showLoader();
            let res = await axios.post("/backend/logout")
            hideLoader();

            if (res.status === 200) {
                successToast(res.data['message'])
                setTimeout(() => {
                    window.location.href = "/login";
                }, 2000)
            } else {
                errorToast(res.data['message'])
            }
        }
    </script>

    @stack('script')
</body>

</html>
