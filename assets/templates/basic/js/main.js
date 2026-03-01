"use strict";
(function ($) {
  // ==========================================
  //      Start Document Ready function
  // ==========================================
  $(document).ready(function () {
    // ============== Header Hide Click On Body Js Start ========
    $(".header-button").on("click", function () {
      $(".body-overlay").toggleClass("show");
    });
    $(".body-overlay").on("click", function () {
      $(".header-button").trigger("click");
      $(this).removeClass("show");
    });
    // =============== Header Hide Click On Body Js End =========

    //============================ Fixed Header Js Start =========
    (function () {
      $(window).on("scroll", function () {
        if ($(window).scrollTop() >= 100) {
          $(".header").addClass("fixed-header");
        } else {
          $(".header").removeClass("fixed-header");
        }
      });
    })();
    // ========================== Header Hide Scroll Bar Js Start =====================
    $(".navbar-toggler.header-button").on("click", function () {
      $("body").toggleClass("scroll-hide-sm");
    });
    $(".body-overlay").on("click", function () {
      $("body").removeClass("scroll-hide-sm");
    });
    // ========================== Header Hide Scroll Bar Js End =====================

    /*==================== custom dropdown select js ====================*/
    $(".custom--dropdown > .custom--dropdown__selected").on(
      "click",
      function () {
        $(this).parent().toggleClass("open");
      }
    );
    $(".custom--dropdown > .dropdown-list > .dropdown-list__item").on(
      "click",
      function () {
        $(
          ".custom--dropdown > .dropdown-list > .dropdown-list__item"
        ).removeClass("selected");
        $(this)
          .addClass("selected")
          .parent()
          .parent()
          .removeClass("open")
          .children(".custom--dropdown__selected")
          .html($(this).html());
      }
    );
    $(document).on("keyup", function (evt) {
      if ((evt.keyCode || evt.which) === 27) {
        $(".custom--dropdown").removeClass("open");
      }
    });
    $(document).on("click", function (evt) {
      if (
        $(evt.target).closest(".custom--dropdown > .custom--dropdown__selected")
          .length === 0
      ) {
        $(".custom--dropdown").removeClass("open");
      }
    });

    /*=============== custom dropdown select js end =================*/

    // ========================= Client Slider Js Start ===============
    $(".brand-wrapper").slick({
      arrows: false,
      infinite: true,
      slidesToShow: 8,
      slidesToScroll: 1,
      speed: 4000,
      cssEase: "linear",
      autoplay: true,
      autoplaySpeed: 0,
      adaptiveHeight: false,
      autoplay: true,
      pauseOnDotsHover: false,
      pauseOnHover: true,
      pauseOnFocus: true,
      responsive: [
        {
          breakpoint: 1399,
          settings: {
            variableWidth: true,
          },
        },
      ],
    });
    // ========================= Client Slider Js End ===================

    // ========================== Small Device Header Menu On Click Dropdown menu collapse Stop Js Start =====================
    $(".dropdown-item").on("click", function () {
      $(this).closest(".dropdown-menu").addClass("d-block");
    });
    // ========================== Small Device Header Menu On Click Dropdown menu collapse Stop Js End =====================

    // ========================== Add Attribute For Bg Image Js Start =====================
    $(".bg-img").css("background-image", function () {
      return `url(${$(this).data("background-image")})`;
    });
    // ========================== Add Attribute For Bg Image Js End =====================

    // ========================== add active class to ul>li top Active current page Js Start =====================
    function dynamicActiveMenuClass(selector) {
      if (!$(selector).length) return;

      let fileName = window.location.pathname.split("/").reverse()[0];
      selector.find("li").each(function () {
        let anchor = $(this).find("a");
        if ($(anchor).attr("href") == fileName) {
          $(this).addClass("active");
        }
      });
      // if any li has active element add class
      selector.children("li").each(function () {
        if ($(this).find(".active").length) {
          $(this).addClass("active");
        }
      });
      // if no file name return
      if ("" == fileName) {
        selector.find("li").eq(0).addClass("active");
      }
    }
    dynamicActiveMenuClass($("ul.sidebar-menu-list"));

    // ========================== add active class to ul>li top Active current page Js End =====================

    // ================== Password Show Hide Js Start ==========
    $(".toggle-password").on("click", function () {
      $(this).toggleClass("fa-eye");
      var input = $($(this).attr("id"));
      if (input.attr("type") == "password") {
        input.attr("type", "text");
      } else {
        input.attr("type", "password");
      }
    });
    // =============== Password Show Hide Js End =================

    // ================== Sidebar Menu Js Start ===============
    // Sidebar Dropdown Menu Start
    $(".has-dropdown > a").click(function () {
      $(".sidebar-submenu").slideUp(200);
      if ($(this).parent().hasClass("active")) {
        $(".has-dropdown").removeClass("active");
        $(this).parent().removeClass("active");
      } else {
        $(".has-dropdown").removeClass("active");
        $(this).next(".sidebar-submenu").slideDown(200);
        $(this).parent().addClass("active");
      }
    });
    // Sidebar Dropdown Menu End
    // Sidebar Icon & Overlay js
    $(".dashboard-body__bar-icon").on("click", function () {
      $(".sidebar-menu").addClass("show-sidebar");
      $(".sidebar-overlay").addClass("show");
    });
    $(".sidebar-menu__close, .sidebar-overlay").on("click", function () {
      $(".sidebar-menu").removeClass("show-sidebar");
      $(".sidebar-overlay").removeClass("show");
    });
    // Sidebar Icon & Overlay js
    // ===================== Sidebar Menu Js End =================

    // ==================== Dashboard User Profile Dropdown Start ==================
    $(".user-info__button").on("click", function () {
      $(".user-info-dropdown").toggleClass("show");
    });
    $(".user-info__button").attr("tabindex", -1).focus();

    $(".user-info__button").on("focusout", function () {
      $(".user-info-dropdown").removeClass("show");
    });
    // ==================== Dashboard User Profile Dropdown End ==================

    // ==================== search-form js Start ==================
    $(".search-icon").on("click", function (e) {
      e.stopPropagation();
      $(".search-form").addClass("show");
    });

    $(".search-form").on("click", function (e) {
      e.stopPropagation();
    });

    $(document).on("click", function () {
      $(".search-form").removeClass("show");
    });

    // ==================== search-form js End ==================

    //Plugin Customization Start
    // ========================= Select2 Js Start ==============
    (function () {
      $(".select2").each((index, select) => {
        $(select)
          .wrap('<div class="select2-wrapper"></div>')
          .select2({
            dropdownParent: $(select).closest(".select2-wrapper"),
          });
      });
    })();
    // ========================= Select2 Js End ==============

    // ========================= Slick Slider Js Start ==============

    $(".testimonial-slider").slick({
      arrows: false,
      infinite: true,
      slidesToShow: 2,
      slidesToScroll: 1,
      speed: 4000,
      cssEase: "linear",
      autoplay: true,
      autoplaySpeed: 0,
      adaptiveHeight: false,
      pauseOnDotsHover: false,
      pauseOnHover: true,
      pauseOnFocus: true,
      dots: true,
      responsive: [
        {
          breakpoint: 1199,
          settings: {
            arrows: false,
            slidesToShow: 2,
            dots: true,
          },
        },
        {
          breakpoint: 991,
          settings: {
            arrows: false,
            slidesToShow: 2,
          },
        },
        {
          breakpoint: 464,
          settings: {
            arrows: false,
            slidesToShow: 1,
          },
        },
      ],
    });
    // ========================= Slick Slider Js End ===================

    // ========================= Light case Js End ==================
    if ($("a[data-rel^=lightcase]").length) {
      $("a[data-rel^=lightcase]").lightcase();
    }
    // ========================= Light case Js End ===================

    //  sidebar js
    $(document).ready(function () {
      $(".sidebar-item__content").each(function () {
        var $block = $(this);
        var $checkboxes = $block.find(".sidebar-item__text");
        var $loadMoreButton = $block.find(".load-more-button");
        var itemsToShow = 8;

        function toggleCheckboxesVisibility() {
          $checkboxes.hide().slice(0, itemsToShow).show();
        }

        $loadMoreButton.on("click", function () {
          itemsToShow = itemsToShow === 8 ? $checkboxes.length : 8;
          $loadMoreButton.text(itemsToShow === 8 ? "Load More" : "Show Less");
          toggleCheckboxesVisibility();
        });

        toggleCheckboxesVisibility();
        $loadMoreButton.toggle($checkboxes.length > itemsToShow);
      });
    });
    //  sidebar js
    // tab js start here ============================
    function updateBar(navItem) {
      var width = navItem.outerWidth() ?? 0;
      var position = navItem.position() ? navItem.position().left : 0;
      $(".tab-bar").css({
        width: width + "px",
        left: position + "px",
      });
    }
    $(".tab-two .nav-link").on("click", function () {
      updateBar($(this));
    });
    var activeNavItem = $(".tab-two .nav-link.active");
    if (activeNavItem) {
      updateBar(activeNavItem);
    }
    // tab js end here ===================================

    // ============ magnific popup Start ======================================
    // var videoItem = $(".play-button");
    // if (videoItem) {
    //   videoItem.magnificPopup({
    //     type: "iframe",
    //   });
    // }
    // ============ magnific popup end ======================================

    // ========================= Odometer Counter Up Js End ==========
    $(".counter-item").each(function () {
      $(this).isInViewport(function (status) {
        if (status === "entered") {
          for (
            var i = 0;
            i < document.querySelectorAll(".odometer").length;
            i++
          ) {
            var el = document.querySelectorAll(".odometer")[i];
            el.innerHTML = el.getAttribute("data-odometer-final");
          }
        }
      });
    });

    // ========================= Odometer Up Counter Js End =====================
  });

  // ==========================================
  //      End Document Ready function
  // ==========================================

  // ========================= Preloader Js Start =====================
  $(window).on("load", function () {
    $(".preloader").fadeOut();
  });
  // ========================= Preloader Js End=====================
})(jQuery);
