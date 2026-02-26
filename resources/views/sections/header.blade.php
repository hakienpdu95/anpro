<header>
	<a class="hidden!" href="{{ home_url('/') }}">{!! $siteName !!}</a>
	<div class="py-4 border border-gray-300 xl:border-0 hidden xl:block header-top">
		<div class="container">
			<div class="xl:flex items-center hidden">
				<div>
				    <a href="/">
				        <img src="./assets/images/logo.png" alt="Logo">
				    </a>
				</div>
			</div>
		</div>
	</div>

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