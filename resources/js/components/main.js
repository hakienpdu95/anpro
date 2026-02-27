(function($) {
    "use strict";

    const $win = $(window);
    const $doc = $(document);
    const $body = $("body");
    const $modalOverlay = $(".modal-overlay");
    const $tabPanes = $(".tab-pane");

    /*------ Product Filter Buttons ------*/
    const productFilterButtons = $(".home-one-product-filter button");
    if (productFilterButtons.length) {
        $(".home-one-product-filter button:nth-child(1)")
            .addClass("btn-primary")
            .removeClass("btn-default outline shadow-none")
            .siblings()
            .removeClass("btn-primary")
            .addClass("btn-default outline  shadow-none");
        $tabPanes.hide();

        $(".tab-pane:nth-child(1)").addClass("active").show();
        $(".home-one-product-filter button").on("click.sellzy", function() {
            $(this)
                .removeClass("btn-default outline shadow-none")
                .addClass("btn-primary")
                .siblings()
                .removeClass("btn-primary")
                .addClass("btn-default outline  shadow-none");
            $tabPanes.removeClass("active fade").hide();
            let activeTab = $(this).attr("data-tab");
            $(`#${activeTab}`).addClass("active fade").fadeIn();
            return false;
        });
    }

     /*------ Sidebar ----*/
     const sidebarMenu = $("#sidebar-menu-btn");
     const sidebar = $("#sidebar");
     const sidebarMenuClose = $("#side-bar-menu-close");
     if (sidebarMenu.length) {
         sidebarMenu.on("click.sellzy", function() {
             $(sidebar).attr("data-state", "open");
             $modalOverlay.attr("data-overlay-for", "#sidebar");
             $body.addClass("overflow-hidden");
             $modalOverlay.fadeIn();
         });
     }

     if (sidebarMenuClose.length) {
         sidebarMenuClose.on("click.sellzy", function() {
             $(sidebar).attr("data-state", "close");
             $body.removeClass("overflow-hidden");
             $modalOverlay.fadeOut();
         });
     }

     if ($modalOverlay.length) {
        $modalOverlay.on("click.sellzy", function() {
            const overlayFor = $(this).attr("data-overlay-for");
            if (overlayFor) {
                $(overlayFor).attr("data-state", "close");
            }
            $body.removeClass("overflow-hidden scrollbar-offset");
            $(this).fadeOut();
            $(this).removeAttr("data-overlay-for");
        });
    }

    /*------ Scroll To Top Button ----*/
    const $scrollToTop = $(".scroll-to-top");
    $win.on("scroll.sellzy", function() {
        if ($win.scrollTop() > 300) {
            $scrollToTop.removeClass("hide").addClass("active");
        } else {
            $scrollToTop.removeClass("active").addClass("hide");
        }
    });

    $scrollToTop.on("click.sellzy", function() {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
        return false;
    });
})(jQuery);