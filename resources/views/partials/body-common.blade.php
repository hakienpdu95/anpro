<!-- Overlay Start -->
<div data-overlay-for="" class="modal-overlay hidden w-full h-screen fixed top-0 left-0 bg-[#161C247A] z-90"></div>
<!-- Overlay End -->

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