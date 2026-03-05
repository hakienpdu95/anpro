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
	<div class="xl:border-0 sticky-header">
		<div class="pb-4 pt-3 block xl:hidden">
			<div class="container">
				<div class="flex justify-between items-center">
					<div>
						<button class="btn btn-default outline shadow-none size-12 rounded-[50px]" id="sidebar-menu-btn">
						    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						        <path d="M20 12L10 12" stroke="#212529" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
						        <path d="M20 5L4 5" stroke="#212529" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
						        <path d="M20 19L4 19" stroke="#212529" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
						    </svg>
						</button>
					</div>
					<div>
						<a href="{{ home_url('/') }}">
					        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-[120px] md:w-[150px]">
					    </a>
					</div>
					<div class="xl:hidden flex items-center gap-x-4">ICON</div>
				</div>
			</div>
		</div>
		<div class="pb-4 block xl:hidden">
			<div class="container">
				<div>
					
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
			<div class="search-bar-wrapper xl:block hidden h-full relative px-3">
			    <button type="button" class="w-full h-full flex justify-center items-center">
			        <span class="text-white">
			            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
			                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
			            </svg>
			        </span>
			    </button>
			    <div class="search-form w-[400px] p-5 bg-white shadow-2xl absolute -left-[300px] top-full z-30">
			        @include('partials.search-form')
			    </div>
			</div>
		</div>
	</div>
</header>