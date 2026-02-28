<article @php(post_class())>
    <header>
        <h2 class="entry-title">
            {!! sage_post_link_open_tag() !!}
                {!! get_the_title() !!}
            </a>
        </h2>

        @include('partials.entry-meta')
    </header>

    <div class="entry-summary">
        @php(the_excerpt())
    </div>
</article>