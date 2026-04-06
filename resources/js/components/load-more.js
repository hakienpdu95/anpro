document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('load-more-btn');
    if (!btn) return;

    let currentOffset = parseInt(btn.dataset.offset || 6);
    const grid = document.getElementById('posts-grid');

    btn.addEventListener('click', async () => {
        const textSpan = btn.querySelector('.btn-text');
        const loadingSpan = btn.querySelector('.loading');
        const originalText = textSpan.innerHTML;

        const startTotal = performance.now();

        btn.disabled = true;
        textSpan.classList.add('hidden');
        loadingSpan.classList.remove('hidden');

        const formData = new FormData();
        formData.append('action', 'load_more_posts');
        formData.append('offset', currentOffset);
        formData.append('nonce', btn.dataset.nonce);

        try {
            const res = await fetch(btn.dataset.ajaxurl, {
                method: 'POST',
                body: formData
            });

            const html = await res.text();
            const hasMore = res.headers.get('X-Has-More') === '1';

            // Insert HTML thuần (nhanh nhất)
            grid.insertAdjacentHTML('beforeend', html);

            currentOffset += 3;
            btn.dataset.offset = currentOffset;

            if (!hasMore) {
                btn.style.display = 'none';
            }
        } catch (err) {
            console.error(err);
        } finally {
            const totalTime = (performance.now() - startTotal).toFixed(2);
            console.log(`🚀 Load More FULL: ${totalTime}ms`);

            btn.disabled = false;
            textSpan.classList.remove('hidden');
            loadingSpan.classList.add('hidden');
            textSpan.innerHTML = originalText;
        }
    });
});