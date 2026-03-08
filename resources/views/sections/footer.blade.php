<footer id="footer" class="bg-[#6697a1] text-white px-3 py-10 mt-10">
    <div class="container">
        <div class="grid grid-cols-12 gap-5">

            <div class="col-span-12 sm:col-span-4">
                <h3 class="text-white text-2xl font-semibold tracking-tight mb-4">Vì Gia Đình</h3>
                <p class="text-sm leading-relaxed mb-8 max-w-md">
                    Chúng tôi là những bậc phụ huynh bình thường, đang cùng nhau xây dựng và gìn giữ hạnh phúc gia đình. Chúng tôi không phải là chuyên gia, mà chỉ là những người đi sưu tầm, chọn lọc và chia sẻ những bài viết thực tế, hữu ích nhất về mang thai, sinh nở, chăm sóc trẻ sơ sinh, nuôi dạy con cái, cho con bú, tâm lý trẻ em và những khoảnh khắc đẹp trong cuộc sống gia đình.
                </p>

                <div>
                    <span class="uppercase text-xs tracking-[1px] font-medium block mb-3">Follow us</span>
                    {!! sage_social_icons('social_navigation', 'flex items-center gap-6 text-3xl') !!}
                </div>
            </div>

            {!! sage_footer_column('footer_column_1') !!}

            {!! sage_footer_column('footer_column_2') !!}

            {!! sage_footer_column('footer_column_3') !!}

            {!! sage_footer_column('footer_column_4') !!}
        </div>

        {{-- BOTTOM BAR --}}
        <div class="mt-16 pt-8 border-t border-zinc-800 flex flex-col md:flex-row items-end justify-between gap-4 text-xs">
            <div>
                © 2026 vigiadinh.vn - Bản quyền được bảo lưu.
            </div>
        </div>
    </div>
</footer>