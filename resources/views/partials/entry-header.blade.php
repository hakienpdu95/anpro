<h1 class="text-sm font-bold md:text-[27px]">{{ get_the_title() }}</h1>
<div class="w-full h-full flex sm:flex-row flex-col sm:justify-between justify-center space-y-3 sm:space-y-0 items-center">
    @include('partials.entry-meta')
    <span class="flex items-center gap-1">
        <strong>{{ sage_views() }}</strong> lượt xem
    </span>
</div>
