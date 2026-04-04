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
            const data = await res.json();

            if (data.success && data.data.html) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.data.html;
                const newItems = tempDiv.children;

                // Fade-in mượt
                Array.from(newItems).forEach((item, i) => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                    grid.appendChild(item);
                    
                    setTimeout(() => {
                        item.style.transition = 'all 0.4s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, i * 80);
                });

                currentOffset += 3;
                btn.dataset.offset = currentOffset;

                if (data.data.has_more === false) {
                    btn.style.display = 'none';
                    console.log('✅ HẾT BÀI – Button ẩn');
                }
            }
        } catch (err) {
            console.error(err);
        } finally {
            console.timeEnd('🚀 Load More AJAX');
            btn.disabled = false;
            textSpan.classList.remove('hidden');
            loadingSpan.classList.add('hidden');
            textSpan.innerHTML = originalText;
        }
    });
});