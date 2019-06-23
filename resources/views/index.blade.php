<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layout.title')

<body>
    @include('components.preloader')
    
    <!-- Page Content -->
    <div class="container-fluid tm-main">
        <div class="row tm-main-row">

            @include('components.sidebar')

            <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12 tm-content">
                    @include('sections.introduction')
                    @include('sections.products')
                    @include('sections.company')
                    @include('sections.contact')
                </div>  

                @include('components.footer-link')
            </div>  <!-- row -->            
        </div>

        @include('layout.preloads')
        @include('layout.scripts')
    </body>
</html>