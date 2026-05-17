(function ($) {
    "use strict";

    var filters = {
        publicCategory: "",
        documentCategory: "",
        documentStart: "",
        documentEnd: ""
    };

    function parseDate(value) {
        if (!value) {
            return null;
        }

        var parts = value.split("-");
        if (parts.length !== 3) {
            return null;
        }

        return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    }

    function withinDateRange(value, start, end) {
        var current = parseDate(value);
        var startDate = parseDate(start);
        var endDate = parseDate(end);

        if (!current) {
            return true;
        }

        if (startDate && current < startDate) {
            return false;
        }

        if (endDate && current > endDate) {
            return false;
        }

        return true;
    }

    if ($.fn.dataTable) {
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var tableId = settings.nTable.id;
            var row = settings.aoData[dataIndex].nTr;
            var rowCategory = ($(row).data("category") || "").toString();
            var rowDate = ($(row).data("date") || "").toString();

            if (tableId === "publicDocumentsTable") {
                return !filters.publicCategory || rowCategory === filters.publicCategory;
            }

            if (tableId === "documentsTable") {
                var categoryMatch = !filters.documentCategory || rowCategory === filters.documentCategory;
                return categoryMatch && withinDateRange(rowDate, filters.documentStart, filters.documentEnd);
            }

            return true;
        });
    }

    function datatableLanguage() {
        return {
            emptyTable: "Belum ada data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            lengthMenu: "Baris _MENU_",
            loadingRecords: "Memuat...",
            processing: "Memproses...",
            search: "Cari:",
            zeroRecords: "Data tidak ditemukan",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        };
    }

    function initTable(selector) {
        if (!$.fn.DataTable || !$(selector).length) {
            return null;
        }

        var isDocumentsTable = selector === "#documentsTable";

        return $(selector).DataTable({
            responsive: !isDocumentsTable,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], ["5", "10", "25", "50", "Semua"]],
            autoWidth: false,
            columnDefs: [
                { orderable: false, targets: "no-sort" }
            ],
            stripeClasses: [],
            language: datatableLanguage(),
            dom: "<'row align-items-center m-b-sm'<'col-md-6'l><'col-md-6'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row align-items-center m-t-sm'<'col-md-5'i><'col-md-7'p>>"
        });
    }

    function confirmAction(options) {
        if (window.Swal && typeof window.Swal.fire === "function") {
            return window.Swal.fire({
                title: options.title,
                text: options.text,
                icon: options.icon,
                showCancelButton: true,
                confirmButtonText: "Ya",
                cancelButtonText: "Batal",
                reverseButtons: true
            }).then(function (result) {
                return result.isConfirmed === true || result.value === true;
            });
        }

        return Promise.resolve(window.confirm(options.title || "Lanjutkan?"));
    }

    var openActionDropdown = null;

    function closeActionDropdown() {
        if (!openActionDropdown) {
            return;
        }

        openActionDropdown.$menu
            .removeClass("show action-dropdown-floating")
            .removeAttr("style");
        openActionDropdown.$toggle
            .removeClass("show")
            .attr("aria-expanded", "false");
        openActionDropdown.$dropdown.removeClass("show");
        openActionDropdown.$placeholder.after(openActionDropdown.$menu);
        openActionDropdown.$placeholder.remove();
        openActionDropdown = null;
    }

    function positionActionDropdown() {
        if (!openActionDropdown) {
            return;
        }

        var rect = openActionDropdown.toggle.getBoundingClientRect();
        var $menu = openActionDropdown.$menu;
        var menuWidth = $menu.outerWidth();
        var left = rect.right + window.pageXOffset - menuWidth;
        var top = rect.bottom + window.pageYOffset + 6;
        var maxLeft = window.pageXOffset + document.documentElement.clientWidth - menuWidth - 8;

        left = Math.max(window.pageXOffset + 8, Math.min(left, maxLeft));

        $menu.css({
            left: left + "px",
            top: top + "px"
        });
    }

    function previewUrlWithCacheBust(url) {
        if (!url) {
            return "";
        }

        var parts = url.split("#");
        var base = parts[0];
        var hash = parts.length > 1 ? "#" + parts.slice(1).join("#") : "";
        var separator = base.indexOf("?") === -1 ? "?" : "&";

        return base + separator + "preview_v=" + Date.now() + hash;
    }

    function scrollToTable(tableId) {
        var table = document.getElementById(tableId);

        if (!table) {
            return;
        }

        var target = table.closest(".card") || table;
        var headerOffset = 96;
        var top = target.getBoundingClientRect().top + window.pageYOffset - headerOffset;

        window.scrollTo({
            top: Math.max(top, 0),
            behavior: "smooth"
        });
    }

    $(function () {
        var publicTable = initTable("#publicDocumentsTable");
        var usersTable = initTable("#usersTable");
        var documentsTable = initTable("#documentsTable");
        var categoriesTable = initTable("#categoriesTable");
        initTable("#recentDocumentsTable");

        $(".search").on("submit", function (event) {
            event.preventDefault();
        });

        $('[data-toggle="tooltip"]').tooltip();

        $("#publicCategoryFilter").on("change", function () {
            filters.publicCategory = this.value;
            if (publicTable) {
                publicTable.draw();
            }
        });

        $("#documentCategoryFilter").on("change", function () {
            filters.documentCategory = this.value;
            if (documentsTable) {
                documentsTable.draw();
            }
        });

        $("#applyDocumentDate").on("click", function () {
            filters.documentStart = $("#documentDateStart").val();
            filters.documentEnd = $("#documentDateEnd").val();
            if (documentsTable) {
                documentsTable.draw();
            }
        });

        $("[data-table-search]").on("focus", function () {
            scrollToTable($(this).data("table-search"));
        });

        $("[data-table-search]").on("input", function () {
            var tableId = $(this).data("table-search");
            var table = $("#" + tableId).DataTable();
            table.search(this.value).draw();
            scrollToTable(tableId);
        });

        $(".js-demo-submit").on("submit", function (event) {
            event.preventDefault();
            $(this).closest(".modal").modal("hide");
        });

        $(document).on("click", "[data-action-dropdown-toggle]", function (event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            var toggle = this;

            if (openActionDropdown && openActionDropdown.toggle === toggle) {
                closeActionDropdown();
                return;
            }

            var $toggle = $(toggle);
            var $dropdown = $toggle.closest(".action-dropdown");
            var $menu = $dropdown.find("[data-action-dropdown-menu]").first();

            if (!$menu.length) {
                return;
            }

            closeActionDropdown();

            var $placeholder = $("<span data-action-dropdown-placeholder></span>");
            $menu.before($placeholder);
            $("body").append($menu);

            openActionDropdown = {
                toggle: toggle,
                $toggle: $toggle,
                $dropdown: $dropdown,
                $menu: $menu,
                $placeholder: $placeholder
            };

            $dropdown.addClass("show");
            $toggle.addClass("show").attr("aria-expanded", "true");
            $menu.addClass("show action-dropdown-floating");
            positionActionDropdown();
        });

        $(document).on("click", function (event) {
            if (!openActionDropdown) {
                return;
            }

            if ($(event.target).closest("[data-action-dropdown-menu], [data-action-dropdown-toggle]").length) {
                return;
            }

            closeActionDropdown();
        });

        $(document).on("click", "[data-action-dropdown-menu] a", function () {
            closeActionDropdown();
        });

        $(window).on("resize scroll", closeActionDropdown);

        $(document).on("click", ".preview-btn", function (event) {
            event.preventDefault();

            var file = $(this).attr("data-file") || $(this).attr("href") || $(this).data("file");
            var $frame = $("#pdfFrame");

            if (!file || file === "#") {
                return;
            }

            $frame
                .removeAttr("sandbox")
                .attr("src", "about:blank");

            $("#previewModal").modal("show");

            setTimeout(function () {
                $frame.attr("src", previewUrlWithCacheBust(file));
            }, 40);
        });

        $("#previewModal").on("hidden.bs.modal", function () {
            $("#pdfFrame").attr("src", "about:blank");
        });

        $(document).on("change", ".js-status-select", function () {
            var select = this;
            var $select = $(select);
            var current = ($select.data("current") || "").toString();
            var form = select.form;
            var selected = select.value;

            if (selected === "ditolak") {
                var $rejectModal = $("#rejectReasonModal");
                if ($rejectModal.length) {
                    $rejectModal.find("[name='id']").val($select.data("id") || $(form).find("[name='id']").val() || "");
                    $rejectModal.find("[name='rejection_reason']").val($select.data("reason") || "");
                    select.value = current;
                    $rejectModal.modal("show");
                    return;
                }
            }

            confirmAction({
                title: "Ubah status dokumen?",
                text: "Status dokumen akan diperbarui.",
                icon: "info"
            }).then(function (confirmed) {
                if (!confirmed) {
                    select.value = current;
                    return;
                }

                if (window.showActionLoading) {
                    window.showActionLoading();
                }

                HTMLFormElement.prototype.submit.call(form);
            });
        });

        $(document).on("click", ".js-rejection-reason", function (event) {
            event.preventDefault();

            var reason = $(this).data("reason") || "Belum ada alasan penolakan.";
            var $modal = $("#rejectionReasonViewModal");

            $modal.find(".rejection-reason-text").text(reason);
            $modal.modal("show");
        });

        $(document).on("click", ".js-delete", function (event) {
            event.preventDefault();

            confirmAction({
                title: "Hapus data ini?",
                text: "Data akan dihapus dari daftar.",
                icon: "warning"
            }).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                var $table = $(event.target).closest("table");
                var table = $table.DataTable();
                table.row($(event.target).closest("tr")).remove().draw(false);
            });
        });

        $(document).on("click", ".js-confirm", function (event) {
            event.preventDefault();

            var $button = $(this);
            var href = $button.data("href") || $button.attr("href");
            var title = $button.data("title") || "Yakin?";
            var text = $button.data("text") || "Aksi ini akan diproses.";
            var icon = $button.data("icon") || "warning";

            confirmAction({
                title: title,
                text: text,
                icon: icon
            }).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                if (window.goWithActionLoading) {
                    window.goWithActionLoading(href);
                    return;
                }

                window.location.href = href;
            });
        });
    });
})(jQuery);
