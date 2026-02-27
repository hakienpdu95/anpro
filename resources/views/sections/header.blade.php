<div class="fixed top-0 left-0 w-[350px] bg-white h-full z-91 px-4 py-6 flex flex-col justify-between gap-y-6 overflow-y-auto shadow-dark-z-24 transition-all duration-250 ease-[cubic-bezier(0.645,0.045,0.355,1)] data-[state=open]:translate-x-0 data-[state=open]:opacity-100 data-[state=open]:visible data-[state=close]:-translate-x-[200px] data-[state=close]:opacity-0 data-[state=close]:invisible" id="sidebar" data-state="close">
	<div>
		<div class="relative pb-6">
			<img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-[100px]">
			<button class="size-7 inline-flex items-center justify-center absolute top-0 right-0 rounded-full bg-[rgba(145,158,171,0.08)]" id="side-bar-menu-close">X</button>
		</div>
		<div class="flex flex-col gap-y-6">
			@if (has_nav_menu('primary_navigation'))
			<nav class="mobile-menu">
				{!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav', 'echo' => false]) !!}
			</nav>
			@endif
		</div>
	</div>
</div>

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