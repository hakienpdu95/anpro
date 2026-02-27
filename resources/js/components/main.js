(function($) {
    "use strict";

    const $win = $(window);
    const $doc = $(document);
    const $body = $("body");
    const $modalOverlay = $(".modal-overlay");
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
})(jQuery);