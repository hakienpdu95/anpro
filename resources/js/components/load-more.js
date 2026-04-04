document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('load-more-btn');
    if (!btn) return;

    let currentOffset = parseInt(btn.dataset.offset || 6);
    const grid = document.getElementById('posts-grid');

    btn.addEventListener('click', async () => {
        const textSpan = btn.querySelector('.btn-text');
        const loadingSpan = btn.querySelector('.loading');
        if (!textSpan || !loadingSpan) return;

        const originalText = textSpan.innerHTML;

        console.time('🚀 Load More AJAX');

        btn.disabled = true;
        textSpan.classList.add('hidden');
        loadingSpan.classList.remove('hidden');

        const formData = new FormData();
        formData.append('action', 'load_more_posts');
        formData.append('offset', currentOffset);
        formData.append('nonce', btn.dataset.nonce);

        try {
            const res = await fetch(btn.dataset.ajaxurl, { method: 'POST', body: formData });
            
            // === DEBUG RAW RESPONSE ===
            const rawText = await res.text();
            console.log('📥 Raw AJAX Response:', rawText.substring(0, 300)); // chỉ 300 ký tự đầu

            const data = JSON.parse(rawText);

            if (data.success) {
                if (data.data.html && data.data.html.trim() !== '') {
                    grid.insertAdjacentHTML('beforeend', data.data.html);
                }

                currentOffset += 3;
                btn.dataset.offset = currentOffset;

                // === ẨN BUTTON KHI HẾT BÀI (đã fix chắc chắn) ===
                if (data.data.has_more === false) {
                    btn.style.display = 'none';
                    console.log('✅ HẾT BÀI – Button đã ẩn hoàn toàn');
                }
            }
        } catch (err) {
            console.error('Load more error:', err);
        } finally {
            console.timeEnd('🚀 Load More AJAX');
            btn.disabled = false;
            textSpan.classList.remove('hidden');
            loadingSpan.classList.add('hidden');
            textSpan.innerHTML = originalText;
        }
    });
});