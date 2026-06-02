export default function toc(initialHeadings) {
    return {
        headings: initialHeadings || [],
        activeId: '',
        open: window.innerWidth >= 1024, // mở mặc định trên desktop

        init() {
            if (!this.headings.length) return;

            // IntersectionObserver – cực tối ưu
            this.observer = new IntersectionObserver(
                (entries) => {
                    for (let entry of entries) {
                        if (entry.isIntersecting) {
                            this.activeId = entry.target.id;
                            return; // chỉ active heading đầu tiên trong viewport
                        }
                    }
                },
                {
                    rootMargin: '-100px 0px -40% 0px', // điều chỉnh theo header của bạn
                    threshold: 0.3
                }
            );

            // Observe tất cả headings
            document.querySelectorAll('h2[id], h3[id], h4[id]').forEach(h => {
                this.observer.observe(h);
            });
        },

        // Cleanup khi Alpine destroy (tối ưu memory)
        destroy() {
            if (this.observer) this.observer.disconnect();
        },

        scrollTo(id) {
            const el = document.getElementById(id);
            if (!el) return;

            const offset = 100; // chiều cao header + padding
            const y = el.getBoundingClientRect().top + window.scrollY - offset;

            window.scrollTo({ top: y, behavior: 'smooth' });

            // Đóng trên mobile sau khi click
            if (window.innerWidth < 1024) this.open = false;
        },

        toggle() {
            this.open = !this.open;
        }
    };
}