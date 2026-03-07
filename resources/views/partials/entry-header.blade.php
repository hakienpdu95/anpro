<h1 class="text-sm font-bold md:text-[27px] text-[#252525]">{{ get_the_title() }}</h1>
<div class="flex sm:flex-row flex-col sm:justify-between justify-center space-y-3 sm:space-y-0 items-center mt-6 mb-6 py-2 border-y border-bdr-clr dark:border-bdr-clr-drk">
    @include('partials.entry-meta')
    <span class="flex items-center gap-1">
        <strong>{{ sage_views() }}</strong> lượt xem
    </span>
</div>
