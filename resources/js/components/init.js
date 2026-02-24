import Splide from '@splidejs/splide';

document.addEventListener('DOMContentLoaded', () => {
    initDynamicSliders();
});

function initDynamicSliders() {
    document.querySelectorAll('.splide:not(.is-initialized)').forEach(slider => {
        // Lazy init khi scroll đến (tiết kiệm tài nguyên cực mạnh)
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    mountSplide(slider);
                    observer.disconnect();
                }
            });
        }, {
            rootMargin: '120px 0px'   // Load trước 120px khi scroll xuống
        });

        observer.observe(slider);
    });
}

function mountSplide(slider) {
    // Lấy config động từ data attribute
    let userConfig = {};
    try {
        userConfig = JSON.parse(slider.dataset.splideConfig || '{}');
    } catch (e) {
        console.warn('⚠️ Splide config JSON không hợp lệ:', slider);
    }

    // Default config tối ưu
    const defaultConfig = {
        type: 'loop',
        perPage: 3,
        perMove: 1,
        gap: '1.5rem',
        autoplay: true,
        interval: 4000,
        pauseOnHover: true,
        arrows: true,
        pagination: true,
        lazyLoad: 'nearby',
        speed: 800,
    };

    const finalConfig = { ...defaultConfig, ...userConfig };

    const splideInstance = new Splide(slider, finalConfig);
    splideInstance.mount();

    slider.classList.add('is-initialized');
}