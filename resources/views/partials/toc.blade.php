@php($headings = get_toc())
@if (!empty($headings))
<nav 
    class="toc" 
    x-data="toc(@json($headings))" 
    x-init="init()"
    aria-label="Mục lục"
>
    <button 
        class="toc__header" 
        @click="toggle()"
        :aria-expanded="open"
        aria-controls="toc-list"
    >
        <h3 class="toc__title">Mục lục</h3>
        <span class="toc__toggle-icon" :class="{ 'toc__toggle-icon--open': open }">↓</span>
    </button>

    <div id="toc-list" class="toc__body" x-show="open" x-transition>
        <ul class="toc__list">
            <template x-for="(item, i) in headings" :key="i">
                <li :class="['toc__item', 'toc__item--level-' + item.level]">
                    <a 
                        :href="'#' + item.id"
                        class="toc__link"
                        :class="{ 'toc__link--active': activeId === item.id }"
                        @click.prevent="scrollTo(item.id)"
                    >
                        <span x-text="item.text"></span>
                    </a>
                </li>
            </template>
        </ul>
    </div>
</nav>
@endif