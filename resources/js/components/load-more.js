document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('load-more-btn');
    if (!btn) return;

    let currentPaged = parseInt(btn.dataset.paged || 2);
    const grid = document.getElementById('posts-grid');

    btn.addEventListener('click', async () => {
        const textSpan = btn.querySelector('.btn-text');
        const loadingSpan = btn.querySelector('.loading');
        if (!textSpan || !loadingSpan) return;

        const originalText = textSpan.innerHTML;

        console.time('🚀 Load More AJAX');   // ← đo thời gian thực

        btn.disabled = true;
        textSpan.classList.add('hidden');
        loadingSpan.classList.remove('hidden');

        const formData = new FormData();
        formData.append('action', 'load_more_posts');
        formData.append('paged', currentPaged);
        formData.append('nonce', btn.dataset.nonce);

        try {
            const res = await fetch(btn.dataset.ajaxurl, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                if (data.data.html) {
                    grid.insertAdjacentHTML('beforeend', data.data.html);
                }

                currentPaged = data.data.next_page;
                btn.dataset.paged = currentPaged;

                // === ẨN BUTTON KHI HẾT BÀI (dù html rỗng) ===
                if (!data.data.has_more) {
                    btn.style.display = 'none';
                }
            }
        } catch (err) {
            console.error('Load more error:', err);
        } finally {
            console.timeEnd('🚀 Load More AJAX');   // ← xem thời gian thực trong console
            btn.disabled = false;
            textSpan.classList.remove('hidden');
            loadingSpan.classList.add('hidden');
            textSpan.innerHTML = originalText;
        }
    });
});