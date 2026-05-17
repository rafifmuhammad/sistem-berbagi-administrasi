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

        return $(selector).DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], ["5", "10", "25", "50", "Semua"]],
            autoWidth: false,
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
                return result.isConfirmed;
            });
        }

        return Promise.resolve(false);
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

        $("[data-table-search]").on("input", function () {
            var tableId = $(this).data("table-search");
            var table = $("#" + tableId).DataTable();
            table.search(this.value).draw();
        });

        $(".js-demo-submit").on("submit", function (event) {
            event.preventDefault();
            $(this).closest(".modal").modal("hide");
        });

        $(".preview-btn").on("click", function (event) {
            event.preventDefault();

            $("#pdfFrame").attr("src", $(this).data("file"));
            $("#previewModal").modal("show");
        });

        $(".js-status-select").on("change", function () {
            var select = this;
            var $select = $(select);
            var current = ($select.data("current") || "").toString();
            var form = select.form;

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

        $(".js-delete").on("click", function (event) {
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

        $(".js-confirm").on("click", function (event) {
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
