<article @php(post_class('arti-deta-info'))>

  @php(the_content())

  @if ($pagination())
    <footer>
      <nav class="page-nav" aria-label="Page">
        {!! $pagination !!}
      </nav>
    </footer>
  @endif

</article>
