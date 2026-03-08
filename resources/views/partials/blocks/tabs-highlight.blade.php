<section class="mb-10">
	<div class="container">
		<div class="mb-5 flex xl:flex-row flex-col gap-y-3 items-center xl:justify-between border-b-2 border-b-[#d46563] head-tab">
		    <div class="flex gap-x-0 overflow-x-scroll lg:overflow-x-visible home-one-product-filter max-w-full">
		        <button data-tab="happy-family" class="btn btn-large py-1 mr-1 px-[12px] btn-primary bg-[#d46563]"> Gia Đình Hạnh Phúc </button>
		        <button data-tab="family-values" class="btn btn-large py-1 mr-1 px-[12px] btn-default bg-[#ebebeb]"> Đạo Đức & Lối Sống Gia Đình </button>
		        <button data-tab="violence-prevention" class="btn btn-large py-1 mr-1 px-[12px] btn-default bg-[#ebebeb]"> Phòng Chống Bạo Lực Gia Đình </button>
		        <button data-tab="family-policy" class="btn btn-large py-1 px-[12px] btn-default bg-[#ebebeb]"> Tin Chính Sách Gia Đình </button>
		    </div>
		</div>

		<div class="tab-content">
		    @php
			// ================== CONFIG TAXONOMY LINH HOẠT – CHUẨN CHO POST & EVENT ==================
			$tab_configs = [
			    'happy-family' => [
			        'slide' => [
			            'post_type'      => 'event',
			            'flags'          => ['hot'],
			            'pinned_first'   => true,
			            'posts_per_page' => 8,
			            // Không cần tax_query = lấy tất cả event có flag 'hot'
			        ],
			        'grid' => [
			            'post_type'      => 'event',
			            'flags'          => ['breaking'],
			            'posts_per_page' => 4,
			        ],
			        'link_type' => 'listing'
			    ],

			    'family-values' => [
			        'slide' => [
			            'post_type'      => 'post',                    // CPT post
			            'flags'          => ['featured'],
			            //'category'       => ['medical-device', 'choi-gem'],          // ← Slug của taxonomy "category" (CPT post)
			            //'category'    => 10,                        // Hoặc dùng term_id nếu thích
			            'tax_query' => [
						    [
						        'taxonomy' => 'category',      // hoặc 'event-categories', 'post_tag'...
						        'field'    => 'term_id',       // hoặc 'slug', 'id', 'name'
						        'terms'    => 10,              // hoặc ['medical-device'], hoặc [12,34]
						        'operator' => 'IN'
						    ]
						],
			            'pinned_first'   => false,
			            'posts_per_page' => 8,
			        ],
			        'grid' => [
			            'post_type'        => 'event',
			            'flags'            => ['hot'],
			            'event-categories' => 'medical-device',        // ← Slug của taxonomy "event-categories" (CPT event)
			            // 'event-categories' => 67,                   // Hoặc dùng term_id
			            'posts_per_page'   => 4,
			        ],
			        'link_type' => 'medical'
			    ],

			    'violence-prevention' => [
			        'slide' => [
			            'post_type'        => 'event',
			            'flags'            => ['first-aid'],
			            'event-categories' => 'first-aid',             // taxonomy event-categories
			            'pinned_first'     => true,
			            'posts_per_page'   => 8,
			        ],
			        'grid' => [
			            'post_type'        => 'event',
			            'flags'            => ['first-aid', 'hot'],
			            'event-categories' => 'first-aid',
			            'posts_per_page'   => 4,
			        ],
			        'link_type' => 'first-aid'
			    ],

			    'family-policy' => [
			        'slide' => [
			            'post_type'      => 'post',                    // CPT post
			            'flags'          => ['diabetic'],
			            'category'       => 'diabetic-care',           // taxonomy category
			            'pinned_first'   => true,
			            'posts_per_page' => 8,
			        ],
			        'grid' => [
			            'post_type'        => 'event',
			            'flags'            => ['diabetic-care'],
			            'event-categories' => 'diabetic-care',         // taxonomy event-categories
			            'posts_per_page'   => 4,
			        ],
			        'link_type' => 'diabetic'
			    ],
			];

			// ================== QUERY + CACHE (KHÔNG CẦN SỬA) ==================
			$tab_data = [];
			$all_posts_for_prefetch = [];

			foreach ($tab_configs as $tab_id => $config) {
			    $slide_posts = \App\Helpers\QueryCache::getCachedAdvancedPosts("slide_{$tab_id}", $config['slide'], 300);
			    $grid_posts  = \App\Helpers\QueryCache::getCachedAdvancedPosts("grid_{$tab_id}",  $config['grid'],  300);

			    $tab_data[$tab_id] = [
			        'slide_posts' => $slide_posts,
			        'grid_posts'  => $grid_posts,
			        'link_type'   => $config['link_type']
			    ];

			    $all_posts_for_prefetch = array_merge($all_posts_for_prefetch, $slide_posts, $grid_posts);
			}

			if (!empty($all_posts_for_prefetch)) {
			    sage_prefetch_link_posts($all_posts_for_prefetch);
			}
			@endphp

		    <!-- Render các tab -->
		    @foreach ($tab_data as $tab_id => $data)
		        <div class="tab-pane {{ $tab_id === 'happy-family' ? 'active' : '' }}" 
		             id="{{ $tab_id }}" 
		             style="{{ $tab_id === 'happy-family' ? '' : 'display: none;' }}">
		            
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