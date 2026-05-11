<!doctype html>
<html lang="id" class="dark">
<head>
  <meta charset="utf-8">
  <title>@yield('title','SVM AMDK')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/app.css','resources/js/app.js']) 
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 text-slate-100">
  @yield('body')  
</body>
</html>
