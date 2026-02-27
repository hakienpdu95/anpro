<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @php(do_action('get_header'))
    @php(wp_head())

    @vite([
      'resources/css/app.css',
      'resources/css/main.scss',
      'resources/js/app.js'
    ])
  </head>

  <body @php(body_class())>
    @php(wp_body_open())

    <div id="app" class="container mx-auto bg-white">
      @include('sections.header')

      <main id="main" class="main">
        <div class="container">
          <div class="grid grid-cols-12 gap-6">
              <div class="xl:col-span-9 col-span-12">
                  @yield('content')
              </div>

              <div class="xl:col-span-3 col-span-12">
                  @hasSection('sidebar')
                    <aside class="sidebar">
                      @yield('sidebar')
                    </aside>
                  @endif
              </div>
          </div>
      </div>
    </main>

    @include('sections.footer')
    </div>

    @php(do_action('get_footer'))
    @php(wp_footer())
    @yield('scripts')
  </body>
</html>
