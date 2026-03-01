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
		    @php
		        // ================== CONFIG SIÊU LINH HOẠT CHO TỪNG TAB ==================
		        // Anh chỉ cần sửa post_type + flags + limit ở đây là xong hết
		        $tab_configs = [
		            'all-products' => [
		                'slide' => [
		                    'post_type' => 'event',
		                    'flags'     => ['hot'],
		                    'limit'     => 8
		                ],
		                'grid' => [
		                    'post_type' => 'event',
		                    'flags'     => ['breaking'],
		                    'limit'     => 4
		                ],
		                'link_type' => 'listing'
		            ],

		            'medical-device' => [
		                'slide' => [
		                    'post_type' => 'post',      // ← anh sửa post_type nếu khác
		                    'flags'     => ['featured'],   // ← anh sửa flags
		                    'limit'     => 8
		                ],
		                'grid' => [
		                    'post_type' => 'event',
		                    'flags'     => ['medical-device'],
		                    'limit'     => 4
		                ],
		                'link_type' => 'medical'
		            ],

		            'first-aid' => [
		                'slide' => [
		                    'post_type' => 'event',
		                    'flags'     => ['first-aid'],
		                    'limit'     => 8
		                ],
		                'grid' => [
		                    'post_type' => 'event',
		                    'flags'     => ['first-aid', 'hot'],
		                    'limit'     => 4
		                ],
		                'link_type' => 'first-aid'
		            ],

		            'diabetic-care' => [
		                'slide' => [
		                    'post_type' => 'post',      // ← anh sửa post_type nếu khác
		                    'flags'     => ['diabetic'],
		                    'limit'     => 8
		                ],
		                'grid' => [
		                    'post_type' => 'event',
		                    'flags'     => ['diabetic-care'],
		                    'limit'     => 4
		                ],
		                'link_type' => 'diabetic'
		            ],
		        ];

		        $tab_data = [];
		        $all_posts_for_prefetch = [];

		        foreach ($tab_configs as $tab_id => $config) {
		            // Query Slide (có thể khác post_type với grid)
		            $slide_posts = \App\Helpers\QueryCache::getPostsWithAllFlags(
		                $config['slide']['post_type'],
		                $config['slide']['flags'],
		                $config['slide']['limit'],
		                300
		            );

		            // Query Grid (có thể khác post_type với slide)
		            $grid_posts = \App\Helpers\QueryCache::getPostsWithAllFlags(
		                $config['grid']['post_type'],
		                $config['grid']['flags'],
		                $config['grid']['limit'],
		                300
		            );

		            $tab_data[$tab_id] = [
		                'slide_posts' => $slide_posts,
		                'grid_posts'  => $grid_posts,
		                'link_type'   => $config['link_type']
		            ];

		            // Thu thập tất cả posts để prefetch 1 lần
		            $all_posts_for_prefetch = array_merge($all_posts_for_prefetch, $slide_posts, $grid_posts);
		        }

		        // BULK PREFETCH – TỐI ƯU HIỆU SUẤT CAO NHẤT
		        if (!empty($all_posts_for_prefetch)) {
		            sage_prefetch_link_posts($all_posts_for_prefetch);
		        }
		    @endphp

		    <!-- Render các tab -->
		    @foreach ($tab_data as $tab_id => $data)
		        <div class="tab-pane {{ $tab_id === 'all-products' ? 'active' : '' }}" 
		             id="{{ $tab_id }}" 
		             style="{{ $tab_id === 'all-products' ? '' : 'display: none;' }}">
		            
		            @include('partials.blocks/home-tab-section', [
		                'slide_posts' => $data['slide_posts'],
		                'grid_posts'  => $data['grid_posts'],
		                'link_type'   => $data['link_type']
		            ])
		        </div>
		    @endforeach
		</div>
	</div>
</section>