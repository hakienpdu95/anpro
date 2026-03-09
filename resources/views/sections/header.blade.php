<header>
	<div class="py-4 xl:border-0 hidden xl:block header-top">
		<div class="container">
			<div class="xl:flex items-center hidden">
				<div>
				    <a href="{{ home_url('/') }}">
				        <img src="{{ asset('images/logo.png') }}" alt="Logo">
				    </a>
				</div>
			</div>
		</div>
	</div>

	<!-- Mobile Menu Start -->
	<div class="xl:border-0 sticky-header bg-[#6697a1]">
		<div class="sm:px-0 px-3 pb-4 pt-3 block xl:hidden">
			<div class="container">
				<div class="flex justify-between items-center">
					<div>
						<button class="btn btn-default border-1 border-[#fff] inline-flex items-center justify-center size-12 rounded-[50px]" id="sidebar-menu-btn">
						    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						        <path d="M20 12L10 12" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
						        <path d="M20 5L4 5" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
						        <path d="M20 19L4 19" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
						    </svg>
						</button>
					</div>
					<div>
						<a href="{{ home_url('/') }}">
					        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-[120px] md:w-[150px]">
					    </a>
					</div>
					<div class="search-bar-wrapper relative inline-flex items-center h-full px-2" x-data="{ open: false }">
					    <!-- Search Icon Link -->
					    <a href="#!" @click.prevent="open = true">
					        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="search" class="text-white lucide lucide-search w-5 h-5 stroke-2 transition-colors duration-300">
					            <circle cx="11" cy="11" r="8"></circle>
					            <path d="m21 21-4.3-4.3"></path>
					        </svg>
					    </a>
					    <!-- Search Modal -->
					    <div x-show="open" class="absolute top-full right-0 w-full z-30" x-transition="" style="display: none;">
					        <div class="bg-white rounded-xs p-3 w-[400px] relative" style="left: calc(100% - 400px); box-shadow: 0px 30px 50px -10px rgba(0, 0, 0, 0.15);">
					            <h2 class="text-lg text-black dark:text-white font-bold mb-4">Tìm kiếm</h2>
					            @include('partials.search-form')
					            <div class="flex justify-end mt-2">
					                <button @click="open = false" class=" bg-black text-white px-4 py-2 absolute top-0 rtl:left-0 ltr:right-0">x</button>
					            </div>
					        </div>
					    </div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Mobile Menu End -->

	<div class="hidden xl:flex header-middle sticky-header border-r-0 border-l-0 bg-[#6697a1]">
		<div class="w-full items-center flex justify-between">
			@if (has_nav_menu('primary_navigation'))
			<nav class="main-menu">
				{!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav', 'echo' => false]) !!}
			</nav>
			@endif
			<div class="search-bar-wrapper relative inline-flex items-center h-full px-2" x-data="{ open: false }">
			    <!-- Search Icon Link -->
			    <a href="#!" @click.prevent="open = true">
			        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="search" class="text-white lucide lucide-search w-5 h-5 stroke-2 transition-colors duration-300">
			            <circle cx="11" cy="11" r="8"></circle>
			            <path d="m21 21-4.3-4.3"></path>
			        </svg>
			    </a>
			    <!-- Search Modal -->
			    <div x-show="open" class="absolute top-full right-0 w-full z-30" x-transition="" style="display: none;">
			        <div class="bg-white rounded-xs p-3 w-[400px] relative" style="left: calc(100% - 400px); box-shadow: 0px 30px 50px -10px rgba(0, 0, 0, 0.15);">
			            <h2 class="text-lg text-black dark:text-white font-bold mb-4">Tìm kiếm</h2>
			            @include('partials.search-form')
			            <div class="flex justify-end mt-2">
			                <button @click="open = false" class=" bg-black text-white px-4 py-2 absolute top-0 rtl:left-0 ltr:right-0">x</button>
			            </div>
			        </div>
			    </div>
			</div>
		</div>
	</div>
</header>