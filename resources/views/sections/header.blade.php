<header>
	<div class="py-4 border border-gray-300 xl:border-0 hidden xl:block header-top">
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
	<div class="border border-gray-300 xl:border-0 sticky-header">
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

	<div class="border border-gray-300 hidden xl:flex header-middle sticky-header border-r-0 border-l-0">
		<div class="container">
			@if (has_nav_menu('primary_navigation'))
			<nav class="main-menu">
				{!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav', 'echo' => false]) !!}
			</nav>
			@endif
			<div></div>
		</div>
	</div>
</header>