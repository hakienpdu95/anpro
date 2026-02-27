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

    /*------ Mobile Menu ----*/
    const mobileMenu = $(".mobile-menu");
    if (mobileMenu.length) {
        mobileMenu
            .find("ul li")
            .parents(".mobile-menu ul li")
            .addClass("has-sub-item")
            .prepend('<span class="submenu-button"></span>'),
            mobileMenu.find(".submenu-button").on("click.sellzy", function() {
                $(this).toggleClass("submenu-opened");
                $(this).siblings("ul").hasClass("open") ?
                    $(this).siblings("ul").removeClass("open").slideUp("fast") :
                    $(this).siblings("ul").addClass("open").slideDown("fast");
            });
    }

    /*------ Main Menu ----*/
    const mainMenu = $(".main-menu");
    if (mainMenu.length) {
        mainMenu.find("ul li").parents(".main-menu ul li").addClass("has-sub-item");
        mainMenu
            .find("ul li.has-sub-item > a")
            .append(
                '<i class="hgi hgi-stroke hgi-arrow-down-01 text-xl text-light-primary-text"></i>'
            );
    }

    /*------ Is Anything Open ------*/
    function isAnythingOpen() {
        const isAnythingOpen = $modalOverlay.attr("data-overlay-for");
        if (isAnythingOpen) {
            $(isAnythingOpen).attr("data-state", "close");
        }
    }

    /*------ Show Sidebar ------*/
    function showSidebar(sidebarFor) {
        $(sidebarFor).attr("data-state", "open");
        $body.addClass("overflow-hidden scrollbar-offset");
        $modalOverlay.fadeIn();
        $modalOverlay.attr("data-overlay-for", sidebarFor);
    }

    /*------ Close Sidebar ------*/
    function closeSidebar(sidebarFor) {
        $(sidebarFor).attr("data-state", "close");
        $body.removeClass("overflow-hidden scrollbar-offset");
        $modalOverlay.fadeOut();
        $modalOverlay.removeAttr("data-overlay-for");
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