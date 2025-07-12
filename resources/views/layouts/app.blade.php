<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Foosball')</title>

  {{-- Bootstrap CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  {{-- Custom CSS --}}
  <link rel="stylesheet" href="{{ asset('css/common.css') }}" type="text/css">

  <style>
    th.sortable {
      cursor: pointer;
    }
    th.sortable:after {
      content: ' \25B2';
      float: right;
      opacity: 0.5;
    }
    th.sortable.desc:after {
      content: ' \25BC';
    }
  </style>

  @stack('head')
</head>
<body>

  @include('partials.navbar')

  <div class="container mt-3">
    @yield('content')
  </div>

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts')
</body>
</html>
