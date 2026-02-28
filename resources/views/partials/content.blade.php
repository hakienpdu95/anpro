<article @php(post_class())>
    {!! sage_post_link_open(get_post(), '', 'archive') !!}   {{-- link_type = 'archive' --}}
        
        <header>
            <h2 class="entry-title">
                {!! get_the_title() !!}  
            </h2>
            @include('partials.entry-meta')
        </header>

        <div class="entry-summary">
            @php(the_excerpt())
        </div>

    {!! sage_post_link_close() !!}
</article>