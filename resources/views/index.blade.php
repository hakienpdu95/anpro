@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="grid grid-cols-12 gap-6">
      <div class="xl:col-span-9 col-span-12">
          @if (! have_posts())
            <x-alert type="warning">
              {!! __('Sorry, no results were found.', 'sage') !!}
            </x-alert>

            {!! get_search_form(false) !!}
          @endif

          @while(have_posts()) @php(the_post())
            @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
          @endwhile

          {!! get_the_posts_navigation() !!}
      </div>
      <div class="xl:col-span-3 col-span-12">
        @section('sidebar')
          @include('sections.sidebar')
        @endsection
      </div>
    </div>
  </div>
@endsection