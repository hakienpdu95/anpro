<section class="pb-[70px]">
	<div class="container">
		<div class="mb-10 flex xl:flex-row flex-col gap-y-4 items-center xl:justify-between wow animate__ animate__fadeInUp animated" data-wow-delay=".2s" style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
		    <div class="flex gap-x-4 overflow-x-scroll lg:overflow-x-visible home-one-product-filter max-w-full">
		        <button data-tab="all-products" class="btn btn-large py-2.5 px-[22px] rounded-full btn-primary"> All Products </button>
		        <button data-tab="medical-device" class="btn btn-large py-2.5 px-[22px] rounded-full btn-default outline shadow-none"> Medical Device </button>
		        <button data-tab="first-aid" class="btn btn-large py-2.5 px-[22px] rounded-full btn-default outline shadow-none"> First Aid </button>
		        <button data-tab="diabetic-care" class="btn btn-large py-2.5 px-[22px] rounded-full btn-default outline shadow-none"> Diabetic Care </button>
		    </div>
		</div>

		<div class="tab-content">
			<div class="tab-pane active" id="all-products">
				<div class="w-full flex flex-col">
					@php
				        $posts = \App\Helpers\QueryCache::getPostsWithAllFlags(
				            'event', 
				            ['hot'], 
				            8,     
				            300    
				        );
				    @endphp

				    @includeCached('partials.blocks.block-slide-tab-one', [
				        'posts' => $posts,
				        'perPage' => 1,
				        'autoplay' => true,
				        'interval' => 4000,
				    ], 300)

					<div class="article-thumb grid sm:grid-cols-2 grid-cols-1 gap-[30px]">	
						<div class="item group">
							<div class="w-full">
								<div class="w-full h-[100px] overflow-hidden relative mb-5">
									{!! get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'w-full h-full object-cover']) !!}
								</div>

								<ul class="flex space-x-5 items-center mb-5">
				                	<li><span class="sm:text-base sm:leading-[27px] text-sm text-primary-100">By Admin</span></li>
				                	<li class="flex sm:space-x-5 space-x-2.5 items-center">
				                		<div class="w-2.5 h-2.5 rounded-full bg-primary-500"></div>
				                		<span class="sm:text-base sm:leading-[27px] text-sm text-primary-100">Category</span>
				                	</li>
				                	<li class="flex sm:space-x-5 space-x-2.5 items-center">
				                		<div class="w-2.5 h-2.5 rounded-full bg-primary-500"></div>
				                		<span class="sm:text-base sm:leading-[27px] text-sm text-primary-100">Comment</span>
				                	</li>
				                </ul>

				                {!! sage_post_link_open(get_post(), 'no-underline!', 'listing') !!}
				                    <h2 class="xl:text-lg xl:leading-7 text-md font-bold spline-sans text-primary-900 mb-5">
				                        {!! get_the_title() !!}
				                    </h2>
				                {!! sage_post_link_close() !!}
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="medical-device" style="display: none;">TAB 2</div>
			<div class="tab-pane" id="first-aid" style="display: none;">TAB 3</div>
			<div class="tab-pane" id="diabetic-care" style="display: none;">TAB 4</div>
		</div>
	</div>
</section>