<footer id="footer" class="bg-[#6697a1] text-white px-3 py-10 mt-10">
    <div class="container">
        <div class="grid grid-cols-12 gap-5">

            {{-- CỘT 1: Info + Social (rộng hơn) --}}
            <div class="col-span-4">
                <h3 class="text-white text-2xl font-semibold tracking-tight mb-4">Vì Gia Đình</h3>
                <p class="text-sm leading-relaxed mb-8 max-w-md">
                    Chúng tôi là những bậc phụ huynh bình thường, đang cùng nhau xây dựng và gìn giữ hạnh phúc gia đình. Chúng tôi không phải là chuyên gia, mà chỉ là những người đi sưu tầm, chọn lọc và chia sẻ những bài viết thực tế, hữu ích nhất về mang thai, sinh nở, chăm sóc trẻ sơ sinh, nuôi dạy con cái, cho con bú, tâm lý trẻ em và những khoảnh khắc đẹp trong cuộc sống gia đình.
                </p>

                <div>
                    <span class="uppercase text-xs tracking-[1px] text-zinc-500 font-medium block mb-3">Follow us</span>
                    <div class="flex gap-3">
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-xl bg-zinc-900 hover:bg-blue-600 transition-all group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-zinc-400 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-xl bg-zinc-900 hover:bg-sky-500 transition-all group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-zinc-400 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-xl bg-zinc-900 hover:bg-red-600 transition-all group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-zinc-400 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-xl bg-zinc-900 hover:bg-rose-500 transition-all group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-zinc-400 group-hover:text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2.01 2.01 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- CỘT 2 --}}
            {!! sage_footer_column('footer_column_1', 'เตรียมเป็นคุณแม่') !!}

            {{-- CỘT 3 --}}
            {!! sage_footer_column('footer_column_2', 'เคล็ดลับการเลี้ยงลูก') !!}

            {{-- CỘT 4 --}}
            {!! sage_footer_column('footer_column_3', 'ไลฟ์สไตล์') !!}

            {{-- CỘT 5 --}}
            {!! sage_footer_column('footer_column_4', 'เกี่ยวกับเรา') !!}
        </div>

        {{-- BOTTOM BAR --}}
        <div class="mt-16 pt-8 border-t border-zinc-800 flex flex-col md:flex-row items-end justify-between gap-4 text-xs text-zinc-500">
            <div>
                © 2015 vigiadinh.com.vn - Bản quyền được bảo lưu.
            </div>
        </div>
    </div>
</footer>